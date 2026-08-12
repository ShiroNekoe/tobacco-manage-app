<?php

namespace App\Imports;

use App\Models\Batch;
use App\Models\BatchInterimSeparation;
use App\Models\BatchOrigin;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\HistoricalYieldReport;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ProcessingReportImporter
{
    protected string $filePath;

    public function __construct(?string $filePath = null)
    {
        if ($filePath && file_exists($filePath)) {
            $this->filePath = $filePath;
        } elseif (file_exists(base_path('database/seeders/data/Processing Report_Rev01.xlsx'))) {
            $this->filePath = base_path('database/seeders/data/Processing Report_Rev01.xlsx');
        } elseif (file_exists(base_path('app/imports/Processing Report_Rev01.xlsx'))) {
            $this->filePath = base_path('app/imports/Processing Report_Rev01.xlsx');
        } else {
            $this->filePath = storage_path('app/imports/Processing Report_Rev01.xlsx');
        }
    }

    public function import(bool $reset = false): array
    {
        if ($reset) {
            $this->resetTransactionTables();
        }

        // 1. Ensure Master Data & Users exist
        $usersMap = $this->seedUsersAndRoles();
        $customer = $this->seedCustomer();

        // Load Spreadsheet
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->filePath);

        $importedBatchesCount = 0;
        $importedSacksCount = 0;
        $importedOriginsCount = 0;
        $importedSeparationsCount = 0;

        // Default Product Type
        $defaultProductType = ProductType::firstOrCreate(
            ['code' => 'RAJANGAN'],
            ['name' => 'RAJANGAN']
        );

        $workersList = [$usersMap['karyawan1'], $usersMap['karyawan2'], $usersMap['karyawan3']];
        $workerIndex = 0;

        // 2. Iterate Over All Sheets
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (! preg_match('/SPR\s*Batch\s*(\d+)/i', trim($sheetName), $matches)) {
                continue;
            }

            $batchNum = (int) $matches[1];
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();

            $batchCode = 'BCH-2026-' . str_pad($batchNum, 4, '0', STR_PAD_LEFT);
            $dnNumber = 'DN-2026-' . str_pad($batchNum, 4, '0', STR_PAD_LEFT);
            $receiptDate = Carbon::now()->subDays(60 - $batchNum);

            $dn = DeliveryNote::firstOrCreate(
                ['dn_number' => $dnNumber],
                [
                    'customer_id' => $customer->id,
                    'delivery_date' => $receiptDate,
                    'status' => 'received',
                ]
            );

            // Temp arrays to store parsed origin sections before batch saving
            $parsedSections = [];
            $currentSection = null;

            $noCol = null;
            $grossCol = null;
            $tareCol = null;
            $nettoCol = null;
            $remarkCol = null;

            $prodQtyCol = null;
            $bitsCol = null;
            $dustCol = null;
            $wasteCol = null;
            $totalQtyCol = null;

            for ($r = 1; $r <= $highestRow; $r++) {
                $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
                $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());

                // Detect new Material Desc Section.
                // NOTE: The origin text can appear in TWO different layouts across sheets:
                //   Layout A: A="1. Material Desc."      B=": KASTURI"          (origin in col B)
                //   Layout B: A="1. Material Desc : LOMBOK P9"  B=""           (origin inline in col A)
                // The old code only ever read $c2, so Layout B rows produced an
                // empty $rawOrigin and silently fell back to 'TEMANGGUNG' for
                // every section written in that format (Batches 5,6,7,11,13-25).
                if (preg_match('/Material\s*Desc\.?\s*:?\s*(.*)$/i', $c1, $descMatch)) {
                    if ($currentSection && ! empty($currentSection['sacks'])) {
                        $parsedSections[] = $currentSection;
                    }

                    // Prefer text captured inline in column A (Layout B).
                    // Fall back to column B when column A has nothing after
                    // "Material Desc" (Layout A).
                    $rawOrigin = trim($descMatch[1]);
                    if ($rawOrigin === '') {
                        $rawOrigin = $c2;
                    }

                    $rawOrigin = preg_replace('/^:\s*/', '', $rawOrigin);
                    $rawOrigin = preg_replace('/^Rajangan\s*/i', '', $rawOrigin);
                    $rawOrigin = trim(str_replace(':', '', $rawOrigin));

                    if (empty($rawOrigin)) {
                        $rawOrigin = 'TEMANGGUNG';
                    }

                    $cleanOriginName = strtoupper($rawOrigin);
                    $originObj = Origin::firstOrCreate(['region_name' => $cleanOriginName]);

                    $currentSection = [
                        'origin' => $originObj,
                        'pack_type' => 'Bale',
                        'sacks' => [],
                        'separation' => null,
                    ];

                    $noCol = $grossCol = $tareCol = $nettoCol = $remarkCol = null;
                    $prodQtyCol = $bitsCol = $dustCol = $wasteCol = $totalQtyCol = null;
                }

                // Header Detection
                for ($c = 1; $c <= 12; $c++) {
                    $val = strtolower(trim((string) $sheet->getCell([$c, $r])->getCalculatedValue()));
                    if ($val === 'no') $noCol = $c;
                    if (str_contains($val, 'gross')) $grossCol = $c;
                    if (str_contains($val, 'tare')) $tareCol = $c;
                    if (str_contains($val, 'netto')) $nettoCol = $c;
                    if (str_contains($val, 'remark')) $remarkCol = $c;

                    if (str_contains($val, 'product qty')) $prodQtyCol = $c;
                    if (str_contains($val, 'bits stem')) $bitsCol = $c;
                    if (str_contains($val, 'dust qty')) $dustCol = $c;
                    if (str_contains($val, 'waste qty')) $wasteCol = $c;
                    if (str_contains($val, 'total qty')) $totalQtyCol = $c;
                }

                // Check for Pack Type in table row
                $c3Val = trim((string) $sheet->getCell([3, $r])->getCalculatedValue());
                if (! empty($c3Val) && ! str_contains(strtolower($c3Val), 'pack type') && ! str_contains(strtolower($c3Val), 'total') && $currentSection) {
                    if (in_array(strtolower($c3Val), ['ball goni', 'bale', 'sack', 'box', 'sak', 'c-48'])) {
                        $currentSection['pack_type'] = $c3Val;
                    }
                }

                // Parse Sack Weighing Row
                if ($currentSection && $noCol && $grossCol && $tareCol && $nettoCol) {
                    $noVal = trim((string) $sheet->getCell([$noCol, $r])->getCalculatedValue());
                    $grossVal = $sheet->getCell([$grossCol, $r])->getCalculatedValue();
                    $tareVal = $sheet->getCell([$tareCol, $r])->getCalculatedValue();
                    $nettoVal = $sheet->getCell([$nettoCol, $r])->getCalculatedValue();
                    $rmkVal = $remarkCol ? trim((string) $sheet->getCell([$remarkCol, $r])->getCalculatedValue()) : '-';

                    if (is_numeric($noVal) && (int) $noVal > 0 && is_numeric($grossVal) && is_numeric($tareVal) && is_numeric($nettoVal)) {
                        if (! str_contains(strtolower($c1), 'grand total') && ! str_contains(strtolower($c1), 'percentage') && ! str_contains(strtolower($c1), 'separation')) {
                            $gVal = round((float) $grossVal, 2);
                            $tVal = round((float) $tareVal, 2);
                            $nVal = max(0, round($gVal - $tVal, 2));

                            $currentSection['sacks'][] = [
                                'sack_number' => (int) $noVal,
                                'gross_kg' => $gVal,
                                'tare_kg' => $tVal,
                                'netto_kg' => $nVal,
                                'remark' => (! empty($rmkVal) && $rmkVal !== '-') ? $rmkVal : 'Normal',
                            ];
                        }
                    }
                }

                // Parse Separation Result Row
                if ($currentSection && $prodQtyCol && $bitsCol && $dustCol && $wasteCol && $totalQtyCol && str_contains(strtolower($c1), 'rajangan')) {
                    $pVal = round((float) $sheet->getCell([$prodQtyCol, $r])->getCalculatedValue(), 2);
                    $bVal = round((float) $sheet->getCell([$bitsCol, $r])->getCalculatedValue(), 2);
                    $dVal = round((float) $sheet->getCell([$dustCol, $r])->getCalculatedValue(), 2);
                    $wVal = round((float) $sheet->getCell([$wasteCol, $r])->getCalculatedValue(), 2);
                    $totVal = round((float) $sheet->getCell([$totalQtyCol, $r])->getCalculatedValue(), 2);

                    if ($pVal > 0 || $bVal > 0 || $dVal > 0) {
                        $currentSection['separation'] = [
                            'product_qty' => $pVal,
                            'bits_stem_qty' => $bVal,
                            'dust_qty' => $dVal,
                            'uncountable_waste_qty' => $wVal,
                            'total_qty' => $totVal,
                        ];
                    }
                }
            }

            if ($currentSection && ! empty($currentSection['sacks'])) {
                $parsedSections[] = $currentSection;
            }

            // Create Batch Record
            $firstOrigin = ! empty($parsedSections) ? $parsedSections[0]['origin'] : Origin::first();
            $firstPackType = ! empty($parsedSections) ? $parsedSections[0]['pack_type'] : 'Bale';

            $batch = Batch::create([
                'batch_code' => $batchCode,
                'customer_id' => $customer->id,
                'delivery_note_id' => $dn->id,
                'product_type_id' => $defaultProductType->id,
                'origin_id' => $firstOrigin->id,
                'pack_type' => $firstPackType,
                'date_of_receipt' => $receiptDate,
                'dn_total_pack' => 0,
                'dn_gross_weight' => 0,
                'dn_tare_weight' => 0,
                'dn_netto_weight' => 0,
                'mrl_total_pack' => 0,
                'mrl_gross_weight' => 0,
                'mrl_tare_weight' => 0,
                'mrl_netto_weight' => 0,
                'discrepancy_dn_vs_mrl_kg' => 0,
                'separation_product_kg' => 0,
                'separation_bits_stem_kg' => 0,
                'separation_dust_kg' => 0,
                'separation_waste_kg' => 0,
                'yield_product_pct' => 0,
                'yield_bits_stem_pct' => 0,
                'yield_dust_pct' => 0,
                'yield_waste_pct' => 0,
                'status' => 'CLOSED',
                'supervisor_approval_status' => Batch::APPROVAL_APPROVED,
                'supervisor_approved_at' => $receiptDate->copy()->addHours(6),
                'supervisor_approved_by_user_id' => $usersMap['supervisor']->id,
                'created_by_user_id' => $usersMap['admin']->id,
                'locked_at' => $receiptDate->copy()->addHours(5),
            ]);

            // Save Sections, Weighing Items & Batch Origins
            $secSackCount = 0;
            $secGross = 0;
            $secTare = 0;
            $secNetto = 0;

            $secProd = 0;
            $secBits = 0;
            $secDust = 0;
            $secWaste = 0;

            foreach ($parsedSections as $sec) {
                $origObj = $sec['origin'];
                $importedOriginsCount++;

                $origNettoSum = 0;
                foreach ($sec['sacks'] as $sItem) {
                    $worker = $workersList[$workerIndex % count($workersList)];
                    $workerIndex++;

                    WeighingItem::create([
                        'batch_id' => $batch->id,
                        'sack_number' => $sItem['sack_number'],
                        'gross_kg' => $sItem['gross_kg'],
                        'tare_kg' => $sItem['tare_kg'],
                        'netto_kg' => $sItem['netto_kg'],
                        'remark' => $sItem['remark'],
                        'created_by_user_id' => $worker->id,
                        'shift' => $worker->shift ?? 'Shift 1',
                        'group' => $worker->group ?? 'Group A',
                    ]);

                    $importedSacksCount++;
                    $secSackCount++;
                    $secGross += $sItem['gross_kg'];
                    $secTare += $sItem['tare_kg'];
                    $secNetto += $sItem['netto_kg'];
                    $origNettoSum += $sItem['netto_kg'];
                }

                // BatchOrigin mapping
                BatchOrigin::create([
                    'batch_id' => $batch->id,
                    'origin_id' => $origObj->id,
                    'allocated_kg' => round($origNettoSum, 2),
                    'remaining_kg' => 0,
                    'status' => 'completed',
                ]);

                // Accumulate Separation Summary
                if ($sec['separation']) {
                    $secProd += $sec['separation']['product_qty'];
                    $secBits += $sec['separation']['bits_stem_qty'];
                    $secDust += $sec['separation']['dust_qty'];
                    $secWaste += $sec['separation']['uncountable_waste_qty'];
                    $importedSeparationsCount++;
                }
            }

            // Calculate Yield Percentages for Batch
            $yieldProd = $secNetto > 0 ? round(($secProd / $secNetto) * 100, 2) : 0;
            $yieldBits = $secNetto > 0 ? round(($secBits / $secNetto) * 100, 2) : 0;
            $yieldDust = $secNetto > 0 ? round(($secDust / $secNetto) * 100, 2) : 0;
            $yieldWaste = max(0, round(100.00 - ($yieldProd + $yieldBits + $yieldDust), 2));

            // Update Batch totals
            $batch->update([
                'dn_total_pack' => $secSackCount,
                'dn_gross_weight' => round($secGross, 2),
                'dn_tare_weight' => round($secTare, 2),
                'dn_netto_weight' => round($secNetto, 2),
                'mrl_total_pack' => $secSackCount,
                'mrl_gross_weight' => round($secGross, 2),
                'mrl_tare_weight' => round($secTare, 2),
                'mrl_netto_weight' => round($secNetto, 2),
                'discrepancy_dn_vs_mrl_kg' => 0.00,
                'separation_product_kg' => round($secProd, 2),
                'separation_bits_stem_kg' => round($secBits, 2),
                'separation_dust_kg' => round($secDust, 2),
                'separation_waste_kg' => round($secWaste, 2),
                'yield_product_pct' => $yieldProd,
                'yield_bits_stem_pct' => $yieldBits,
                'yield_dust_pct' => $yieldDust,
                'yield_waste_pct' => $yieldWaste,
            ]);

            $importedBatchesCount++;
        }

        // 3. Import Historical Yield Summary Tables ('By Product' and 'AVG')
        $this->importHistoricalYieldReports($spreadsheet);

        return [
            'batches' => $importedBatchesCount,
            'sacks' => $importedSacksCount,
            'origins' => $importedOriginsCount,
            'separations' => $importedSeparationsCount,
        ];
    }

    protected function importHistoricalYieldReports($spreadsheet): void
    {
        // Import 'By Product' sheet
        $byProductSheet = $spreadsheet->getSheetByName('By Product');
        if ($byProductSheet) {
            $highestRow = $byProductSheet->getHighestRow();
            $currentCategory = 'Yield (Kg)';

            for ($r = 1; $r <= $highestRow; $r++) {
                $c1 = trim((string) $byProductSheet->getCell([1, $r])->getCalculatedValue());
                $c2 = trim((string) $byProductSheet->getCell([2, $r])->getCalculatedValue());
                $c3 = trim((string) $byProductSheet->getCell([3, $r])->getCalculatedValue());

                if (str_contains(strtolower($c1), 'yield historical')) $currentCategory = 'Yield (Kg)';
                if (str_contains(strtolower($c1), 'bits stem historical')) $currentCategory = 'Bits Stem (Kg)';
                if (str_contains(strtolower($c1), 'dust historical')) $currentCategory = 'Dust (Kg)';
                if (str_contains(strtolower($c1), 'accountable waste')) $currentCategory = 'Accountable Waste (Kg)';

                if (is_numeric($c1) && ! empty($c3) && ! str_contains(strtolower($c3), 'total')) {
                    $batchData = [];
                    for ($b = 1; $b <= 25; $b++) {
                        $colIdx = $b + 3; // Col 4 is BATCH 1
                        $val = $byProductSheet->getCell([$colIdx, $r])->getCalculatedValue();
                        if (is_numeric($val)) {
                            $batchData['BATCH ' . $b] = round((float) $val, 2);
                        }
                    }

                    HistoricalYieldReport::create([
                        'report_type' => 'by_product',
                        'row_number' => (int) $c1,
                        'product' => $c2 ?: 'RAJANGAN',
                        'origin' => strtoupper($c3),
                        'metric_category' => $currentCategory,
                        'batch_data' => $batchData,
                    ]);
                }
            }
        }

        // Import 'AVG' sheet
        $avgSheet = $spreadsheet->getSheetByName('AVG');
        if ($avgSheet) {
            $highestRow = $avgSheet->getHighestRow();
            for ($r = 2; $r <= $highestRow; $r++) {
                $c1 = trim((string) $avgSheet->getCell([1, $r])->getCalculatedValue());
                $c2 = trim((string) $avgSheet->getCell([2, $r])->getCalculatedValue());
                $c3 = trim((string) $avgSheet->getCell([3, $r])->getCalculatedValue());
                $c4 = $avgSheet->getCell([4, $r])->getCalculatedValue();
                $c5 = $avgSheet->getCell([5, $r])->getCalculatedValue();

                if (is_numeric($c1) && ! empty($c3)) {
                    HistoricalYieldReport::create([
                        'report_type' => 'avg',
                        'row_number' => (int) $c1,
                        'product' => $c2 ?: 'RAJANGAN',
                        'origin' => strtoupper($c3),
                        'metric_category' => 'Average Yield Summary',
                        'total_qty' => is_numeric($c4) ? round((float) $c4, 2) : 0,
                        'avg_pct' => is_numeric($c5) ? round((float) $c5 * 100, 2) : 0,
                    ]);
                }
            }
        }
    }

    public function resetTransactionTables(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('weighing_items')) WeighingItem::query()->delete();
        if (Schema::hasTable('batch_interim_separations')) BatchInterimSeparation::query()->delete();
        if (Schema::hasTable('batch_origins')) BatchOrigin::query()->delete();
        if (Schema::hasTable('delivery_notes')) DeliveryNote::query()->delete();
        if (Schema::hasTable('batches')) Batch::query()->delete();
        if (Schema::hasTable('historical_yield_reports')) HistoricalYieldReport::query()->delete();

        Schema::enableForeignKeyConstraints();
    }

    public function seedUsersAndRoles(): array
    {
        // 1. Admin
        $admin1 = User::firstOrCreate(['email' => 'admin@factory.com'], [
            'name' => 'Admin Factory Operations',
            'role' => User::ROLE_ADMIN,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate(['email' => 'admin@tobacco.com'], [
            'name' => 'Admin TPMS Fallback',
            'role' => User::ROLE_ADMIN,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        // 2. Supervisor
        $supervisor1 = User::firstOrCreate(['email' => 'supervisor@factory.com'], [
            'name' => 'Supervisor Quality Gate',
            'role' => User::ROLE_SUPERVISOR,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate(['email' => 'supervisor@tobacco.com'], [
            'name' => 'Supervisor QC Fallback',
            'role' => User::ROLE_SUPERVISOR,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        // 3. Karyawan Worker Accounts
        $karyawan1 = User::firstOrCreate(['email' => 'karyawan.a1@factory.com'], [
            'name' => 'Karyawan A1 (Shift 1 Group A)',
            'role' => User::ROLE_KARYAWAN,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        $karyawan2 = User::firstOrCreate(['email' => 'karyawan.b1@factory.com'], [
            'name' => 'Karyawan B1 (Shift 1 Group B)',
            'role' => User::ROLE_KARYAWAN,
            'shift' => 'Shift 1',
            'group' => 'Group B',
            'password' => Hash::make('password'),
        ]);

        $karyawan3 = User::firstOrCreate(['email' => 'karyawan.c2@factory.com'], [
            'name' => 'Karyawan C2 (Shift 2 Group C)',
            'role' => User::ROLE_KARYAWAN,
            'shift' => 'Shift 2',
            'group' => 'Group C',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate(['email' => 'karyawan@tobacco.com'], [
            'name' => 'Budi Santoso (Worker Fallback)',
            'role' => User::ROLE_KARYAWAN,
            'shift' => 'Shift 1',
            'group' => 'Group A',
            'password' => Hash::make('password'),
        ]);

        // 4. Customer Account
        $customerObj = $this->seedCustomer();

        User::firstOrCreate(['email' => 'bimo@falihnurgemilang.com'], [
            'name' => 'Bpk. Bimo (PT. Falih Nur Gemilang)',
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customerObj->id,
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate(['email' => 'customer@tobacco.com'], [
            'name' => 'Customer Fallback',
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customerObj->id,
            'password' => Hash::make('password'),
        ]);

        return [
            'admin' => $admin1,
            'supervisor' => $supervisor1,
            'karyawan1' => $karyawan1,
            'karyawan2' => $karyawan2,
            'karyawan3' => $karyawan3,
        ];
    }

    public function seedCustomer(): Customer
    {
        return Customer::firstOrCreate(['code' => 'CUST-FNG'], [
            'name' => 'Bpk. Bimo (PT. Falih Nur Gemilang)',
            'contact_person' => 'Bpk. Bimo',
            'phone' => '081234567890',
            'address' => 'Surabaya, Jawa Timur',
        ]);
    }
}