<?php

namespace App\Imports;

use App\Models\Batch;
use App\Models\BatchOrigin;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\DnShipment;
use App\Models\DnShipmentItem;
use App\Models\HistoricalYieldReport;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ProcessingReportImporter
{
    protected string $filePath;

    public function __construct(?string $filePath = null)
    {
        if ($filePath && file_exists($filePath)) {
            $this->filePath = $filePath;
        } elseif ($filePath && file_exists(base_path($filePath))) {
            $this->filePath = base_path($filePath);
        } else {
            $baseName = $filePath ? basename($filePath) : '';
            $candidates = array_filter([
                $filePath ? base_path('app/Imports/' . $baseName) : null,
                $filePath ? base_path('app/Imports/' . str_replace('_', ' ', $baseName)) : null,
                $filePath ? base_path('app/Imports/' . str_replace(' ', '_', $baseName)) : null,
                base_path('app/Imports/Processing Report_DAVEN.xlsx'),
                base_path('app/Imports/Processing_Report_DAVEN.xlsx'),
                base_path('app/imports/Processing Report_DAVEN.xlsx'),
                base_path('database/seeders/data/Processing Report_Rev01.xlsx'),
                base_path('app/Imports/Processing Report_Rev01.xlsx'),
                storage_path('app/imports/Processing Report_Rev01.xlsx'),
            ]);

            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    $this->filePath = $candidate;
                    break;
                }
            }

            if (empty($this->filePath)) {
                $this->filePath = $filePath ?? base_path('app/Imports/Processing Report_DAVEN.xlsx');
            }
        }
    }

    public function import(bool $reset = false): array
    {
        if ($reset) {
            $this->resetProcessingTables();
        }

        // Load Excel
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->filePath);

        $importedBatchesCount = 0;
        $importedSacksCount = 0;
        $importedOriginsCount = 0;

        // Default Product Type
        $defaultProductType = ProductType::firstOrCreate(
            ['code' => 'RAJANGAN'],
            ['name' => 'RAJANGAN']
        );

        // Get or create dummy customer
        $customer = Customer::firstOrCreate(
            ['code' => 'CUST-FNG'],
            [
                'name' => 'PT. Falih Nur Gemilang',
                'contact_person' => 'Bpk. Bimo',
                'phone' => '081234567890',
                'address' => 'Surabaya, Jawa Timur',
            ]
        );

        // ===== ITERATE BATCH SHEETS 1-25 =====
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (!preg_match('/SPR\s*Batch\s*(\d+)/i', trim($sheetName), $matches)) {
                continue;
            }

            $batchNum = (int) $matches[1];
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();

            echo "Processing Batch {$batchNum}...\n";

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

            // Parse sections & material code mapping
            $parsedSections = [];
            $currentSection = null;

            // Header columns
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

                // ===== DETECT MATERIAL DESC (FLEXIBLE FORMAT) =====
                if (preg_match('/Material\s*Desc\.?\s*:?\s*(.*)$/i', $c1, $descMatch)) {
                    // Save previous section
                    if ($currentSection && !empty($currentSection['sacks'])) {
                        $parsedSections[] = $currentSection;
                    }

                    // Parse origin + material code (FLEXIBLE)
                    $rawOrigin = trim($descMatch[1]);
                    if (empty($rawOrigin)) {
                        $rawOrigin = $c2;
                    }

                    // Clean up
                    $rawOrigin = preg_replace('/^:\s*/', '', $rawOrigin);
                    $rawOrigin = preg_replace('/^Rajangan\s*/i', '', $rawOrigin);
                    $rawOrigin = trim(str_replace(':', '', $rawOrigin));

                    if (empty($rawOrigin)) {
                        $rawOrigin = 'TEMANGGUNG';
                    }

                    // ===== PARSE ORIGIN + CODE (FLEXIBLE) =====
                    [$cleanOrigin, $materialCode] = $this->parseOriginAndCode($rawOrigin);

                    $originObj = Origin::firstOrCreate(['region_name' => $cleanOrigin]);

                    $currentSection = [
                        'origin' => $originObj,
                        'origin_name' => $cleanOrigin,
                        'material_code' => $materialCode,
                        'pack_type' => 'Bale',
                        'sacks' => [],
                        'separation' => null,
                    ];

                    $noCol = $grossCol = $tareCol = $nettoCol = $remarkCol = null;
                    $prodQtyCol = $bitsCol = $dustCol = $wasteCol = $totalQtyCol = null;
                }

                // DETECT HEADER ROW
                for ($c = 1; $c <= 12; $c++) {
                    $val = strtolower(trim((string) $sheet->getCell([$c, $r])->getCalculatedValue()));
                    if ($val === 'no') $noCol = $c;
                    if (str_contains($val, 'gross')) $grossCol = $c;
                    if (str_contains($val, 'tare')) $tareCol = $c;
                    if (str_contains($val, 'netto')) $nettoCol = $c;
                    if (str_contains($val, 'remark')) $remarkCol = $c;

                    if (str_contains($val, 'product qty')) $prodQtyCol = $c;
                    if (str_contains($val, 'bits stem') || str_contains($val, 'bits/stem')) $bitsCol = $c;
                    if (str_contains($val, 'dust qty')) $dustCol = $c;
                    if (str_contains($val, 'waste qty') || str_contains($val, 'uncountable')) $wasteCol = $c;
                    if (str_contains($val, 'total qty')) $totalQtyCol = $c;
                }

                // DETECT PACK TYPE
                $c3Val = trim((string) $sheet->getCell([3, $r])->getCalculatedValue());
                if (!empty($c3Val) && !str_contains(strtolower($c3Val), 'pack type') && !str_contains(strtolower($c3Val), 'total') && $currentSection) {
                    if (in_array(strtolower($c3Val), ['ball goni', 'bale', 'sack', 'box', 'sak', 'c-48', 'ball'])) {
                        $currentSection['pack_type'] = $c3Val;
                    }
                }

                // PARSE SACK WEIGHING ROW
                if ($currentSection && $noCol && $grossCol && $tareCol && $nettoCol) {
                    $noVal = trim((string) $sheet->getCell([$noCol, $r])->getCalculatedValue());
                    $grossVal = $sheet->getCell([$grossCol, $r])->getCalculatedValue();
                    $tareVal = $sheet->getCell([$tareCol, $r])->getCalculatedValue();
                    $nettoVal = $sheet->getCell([$nettoCol, $r])->getCalculatedValue();
                    $rmkVal = $remarkCol ? trim((string) $sheet->getCell([$remarkCol, $r])->getCalculatedValue()) : '-';

                    if (is_numeric($noVal) && (int)$noVal > 0 && is_numeric($grossVal) && is_numeric($tareVal) && is_numeric($nettoVal)) {
                        if (!str_contains(strtolower($c1), 'grand total') && !str_contains(strtolower($c1), 'percentage') && !str_contains(strtolower($c1), 'separation')) {
                            $gVal = round((float) $grossVal, 2);
                            $tVal = round((float) $tareVal, 2);
                            $nVal = max(0, round($gVal - $tVal, 2));

                            $currentSection['sacks'][] = [
                                'sack_number' => (int) $noVal,
                                'gross_kg' => $gVal,
                                'tare_kg' => $tVal,
                                'netto_kg' => $nVal,
                                'remark' => (!empty($rmkVal) && $rmkVal !== '-') ? $rmkVal : 'Normal',
                            ];
                        }
                    }
                }

                // PARSE SEPARATION RESULT ROW
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

            // Save last section
            if ($currentSection && !empty($currentSection['sacks'])) {
                $parsedSections[] = $currentSection;
            }

            // ===== CREATE BATCH RECORD =====
            $firstOrigin = !empty($parsedSections) ? $parsedSections[0]['origin'] : Origin::first();
            $firstPackType = !empty($parsedSections) ? $parsedSections[0]['pack_type'] : 'Bale';
            $firstMaterialCode = !empty($parsedSections) ? $parsedSections[0]['material_code'] : 'N/A';

            $batch = Batch::create([
                'batch_code' => $batchCode,
                'customer_id' => $customer->id,
                'delivery_note_id' => $dn->id,
                'product_type_id' => $defaultProductType->id,
                'origin_id' => $firstOrigin->id,
                'material_code' => $firstMaterialCode,  // ← SEPARATE
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
                'status' => 'CLOSED',
                'supervisor_approval_status' => Batch::APPROVAL_APPROVED,
                'locked_at' => $receiptDate->copy()->addHours(5),
            ]);

            // ===== PROCESS SECTIONS & CALCULATE BALANCE =====
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
                    WeighingItem::create([
                        'batch_id' => $batch->id,
                        'sack_number' => $sItem['sack_number'],
                        'gross_kg' => $sItem['gross_kg'],
                        'tare_kg' => $sItem['tare_kg'],
                        'netto_kg' => $sItem['netto_kg'],
                        'remark' => $sItem['remark'],
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

                // Accumulate Separation data
                if ($sec['separation']) {
                    $secProd += $sec['separation']['product_qty'];
                    $secBits += $sec['separation']['bits_stem_qty'];
                    $secDust += $sec['separation']['dust_qty'];
                    $secWaste += $sec['separation']['uncountable_waste_qty'];
                }
            }

            // ===== MATERIAL BALANCE CALCULATION (100% BALANCE) =====
            $processedInputNetto = $secNetto;

            // Ensure 100% balance: redistribute rounding errors
            $totalSeparation = $secProd + $secBits + $secDust + $secWaste;
            $balanceDifference = round($processedInputNetto - $totalSeparation, 2);

            // Adjust waste to maintain 100% balance
            if (abs($balanceDifference) > 0.01) {
                $secWaste = round($secWaste + $balanceDifference, 2);
                $secWaste = max(0, $secWaste);
            }

            // Recalculate percentages
            $yieldProd = $processedInputNetto > 0 ? round(($secProd / $processedInputNetto) * 100, 2) : 0;
            $yieldBits = $processedInputNetto > 0 ? round(($secBits / $processedInputNetto) * 100, 2) : 0;
            $yieldDust = $processedInputNetto > 0 ? round(($secDust / $processedInputNetto) * 100, 2) : 0;
            $yieldWaste = max(0, round(100 - ($yieldProd + $yieldBits + $yieldDust), 2));

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

        $this->importHistoricalYieldReports($spreadsheet);
        $this->seedSampleDnShipments();

        echo "\n✅ Import complete!\n";
        return [
            'batches' => $importedBatchesCount,
            'sacks' => $importedSacksCount,
            'origins' => $importedOriginsCount,
            'separations' => $importedBatchesCount,
            'shipments' => DnShipment::count(),
        ];
    }

    /**
     * Parse origin dan material code dari raw string
     * Handle multiple formats:
     * - "KASTURI FN602" → [KASTURI, FN602]
     * - "LOMBOK (P9K5)" → [LOMBOK, P9K5]
     * - "KASTURI" → [KASTURI, DEFAULT]
     * - "REMBANG (P8B4)" → [REMBANG, P8B4]
     */
    protected function parseOriginAndCode(string $rawOrigin): array
    {
        $raw = strtoupper(trim($rawOrigin));

        // Clean up prefixes
        $raw = preg_replace('/^:\s*/', '', $raw);
        $raw = preg_replace('/^RAJANGAN\s*/i', '', $raw);
        $raw = trim(str_replace(':', '', $raw));

        if (empty($raw)) {
            return ['TEMANGGUNG', 'DEFAULT'];
        }

        $knownRegions = [
            'KASTURI',
            'LOMBOK',
            'MADURA',
            'MAESAN',
            'PAITON',
            'PLOSO',
            'REMBANG',
            'TEMANGGUNG',
        ];

        foreach ($knownRegions as $region) {
            if ($raw === $region) {
                return [$region, 'DEFAULT'];
            }
            if (str_starts_with($raw, $region)) {
                $suffix = trim(substr($raw, strlen($region)));
                $suffix = trim($suffix, " \t\n\r\0\x0B()");
                if (!empty($suffix)) {
                    $code = $suffix;
                    if (preg_match('/^[\'’]?(\d{2})$/', $suffix, $m)) {
                        $code = $region . "'" . $m[1];
                    }
                    return [$region, $code];
                }
                return [$region, 'DEFAULT'];
            }
        }

        return [$raw, 'DEFAULT'];
    }

    protected function importHistoricalYieldReports(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getSheetByName('By Product');
        if (!$sheet) {
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $currentMetric = 'Yield (Kg)';
        $rowNo = 1;

        for ($r = 1; $r <= $highestRow; $r++) {
            $c1 = trim((string)$sheet->getCell([1, $r])->getValue());

            if (str_contains(strtolower($c1), 'yield historical')) {
                $currentMetric = 'Yield (Kg)';
                $rowNo = 1;
                continue;
            } elseif (str_contains(strtolower($c1), 'bits stem historical')) {
                $currentMetric = 'Bits Stem (Kg)';
                $rowNo = 1;
                continue;
            } elseif (str_contains(strtolower($c1), 'dust historical')) {
                $currentMetric = 'Dust (Kg)';
                $rowNo = 1;
                continue;
            } elseif (str_contains(strtolower($c1), 'accountable waste historical') || str_contains(strtolower($c1), 'waste historical')) {
                $currentMetric = 'Waste (Kg)';
                $rowNo = 1;
                continue;
            }

            $c2 = strtoupper(trim((string)$sheet->getCell([2, $r])->getValue()));
            $c3 = strtoupper(trim((string)$sheet->getCell([3, $r])->getValue()));

            if ($c2 === 'RAJANGAN' && !empty($c3) && $c3 !== 'ORIGIN') {
                $batchData = [];
                for ($b = 1; $b <= 25; $b++) {
                    $col = 3 + $b;
                    $val = $sheet->getCell([$col, $r])->getValue();
                    if (is_numeric($val)) {
                        $batchData['batch_' . $b] = round((float)$val, 2);
                    }
                }

                HistoricalYieldReport::create([
                    'report_type' => 'by_product',
                    'row_number' => $rowNo,
                    'product' => 'RAJANGAN',
                    'origin' => $c3,
                    'metric_category' => $currentMetric,
                    'batch_data' => $batchData,
                ]);

                $rowNo++;
            }
        }
    }

    /**
     * Seed sample Outbound DN Shipments for testing/demo
     */
    protected function seedSampleDnShipments(): void
    {
        if (DnShipment::count() > 0) {
            return;
        }

        $customer = Customer::where('name', 'like', '%Falih%')->first() ?? Customer::first();
        $productType = ProductType::where('code', 'RAJANGAN')->first() ?? ProductType::first();

        $batch24 = Batch::where('batch_code', 'BCH-2026-0024')->first();
        $batch25 = Batch::where('batch_code', 'BCH-2026-0025')->first();
        $batch21 = Batch::where('batch_code', 'BCH-2026-0021')->first();

        // 1. Shipment Outbound 1 - Approved by Customer
        $shipment1 = DnShipment::create([
            'dn_number' => 'DN-OUT-2026-0001',
            'shipment_date' => Carbon::now()->subDays(5),
            'customer_id' => $customer?->id,
            'product_type_id' => $productType?->id,
            'vehicle_number' => 'B 9482 FNG',
            'driver_name' => 'Bambang Haryanto',
            'destination' => 'Gudang Utama Malang, Jawa Timur',
            'notes' => 'Pengiriman Hasil Olahan Rajangan Batch 24 & 25',
            'total_sacks' => 61,
            'total_gross_kg' => 2849.10,
            'total_tare_kg' => 12.20,
            'total_netto_kg' => 2836.90,
            'status' => 'Approved',
            'customer_approved_at' => Carbon::now()->subDays(4),
            'customer_approval_note' => 'Barang diterima lengkap dan sesuai spesifikasi.',
        ]);

        if ($shipment1 && $batch24 && $batch25) {
            DnShipmentItem::create([
                'dn_shipment_id' => $shipment1->id,
                'batch_id' => $batch24->id,
                'batch_code' => $batch24->batch_code,
                'item_no' => 1,
                'origin' => 'Temanggung',
                'origin_code' => 'FN504',
                'material_type' => 'Product',
                'standard_sack_count' => 8,
                'standard_gross_per_sack' => 129.18,
                'standard_tare_per_sack' => 0.20,
                'standard_netto_per_sack' => 128.98,
                'has_remnant' => false,
                'remnant_gross_kg' => 0.00,
                'remnant_tare_kg' => 0.00,
                'remnant_netto_kg' => 0.00,
                'total_sacks' => 8,
                'total_gross_kg' => 1033.50,
                'total_tare_kg' => 1.60,
                'total_netto_kg' => 1031.90,
            ]);

            DnShipmentItem::create([
                'dn_shipment_id' => $shipment1->id,
                'batch_id' => $batch25->id,
                'batch_code' => $batch25->batch_code,
                'item_no' => 2,
                'origin' => 'Paiton',
                'origin_code' => 'P10T5',
                'material_type' => 'Product',
                'standard_sack_count' => 53,
                'standard_gross_per_sack' => 34.25,
                'standard_tare_per_sack' => 0.20,
                'standard_netto_per_sack' => 34.05,
                'has_remnant' => false,
                'remnant_gross_kg' => 0.00,
                'remnant_tare_kg' => 0.00,
                'remnant_netto_kg' => 0.00,
                'total_sacks' => 53,
                'total_gross_kg' => 1815.60,
                'total_tare_kg' => 10.60,
                'total_netto_kg' => 1805.00,
            ]);

            $shipment1->recalculateTotals();
        }

        // 2. Shipment Outbound 2 - Shipped (Pending Approval)
        $shipment2 = DnShipment::create([
            'dn_number' => 'DN-OUT-2026-0002',
            'shipment_date' => Carbon::now()->subDays(2),
            'customer_id' => $customer?->id,
            'product_type_id' => $productType?->id,
            'vehicle_number' => 'N 8102 UC',
            'driver_name' => 'Sugiarto',
            'destination' => 'Pabrik Cigarette Pasuruan',
            'notes' => 'Pengiriman dalam perjalanan armada ekspedisi Pasuruan',
            'total_sacks' => 40,
            'total_gross_kg' => 2010.70,
            'total_tare_kg' => 8.00,
            'total_netto_kg' => 2002.70,
            'status' => 'Shipped',
        ]);

        if ($shipment2 && $batch21) {
            DnShipmentItem::create([
                'dn_shipment_id' => $shipment2->id,
                'batch_id' => $batch21->id,
                'batch_code' => $batch21->batch_code,
                'item_no' => 1,
                'origin' => 'Lombok',
                'origin_code' => "'25",
                'material_type' => 'Product',
                'standard_sack_count' => 40,
                'standard_gross_per_sack' => 50.26,
                'standard_tare_per_sack' => 0.20,
                'standard_netto_per_sack' => 50.06,
                'has_remnant' => false,
                'remnant_gross_kg' => 0.00,
                'remnant_tare_kg' => 0.00,
                'remnant_netto_kg' => 0.00,
                'total_sacks' => 40,
                'total_gross_kg' => 2010.70,
                'total_tare_kg' => 8.00,
                'total_netto_kg' => 2002.70,
            ]);

            $shipment2->recalculateTotals();
        }
    }

    /**
     * Reset hanya transaction/processing tables
     */
    protected function resetProcessingTables(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('dn_shipment_items')) DnShipmentItem::query()->delete();
        if (Schema::hasTable('dn_shipments')) DnShipment::query()->delete();
        if (Schema::hasTable('weighing_items')) WeighingItem::query()->delete();
        if (Schema::hasTable('batch_origins')) BatchOrigin::query()->delete();
        if (Schema::hasTable('historical_yield_reports')) HistoricalYieldReport::query()->delete();
        if (Schema::hasTable('batches')) Batch::query()->delete();
        if (Schema::hasTable('delivery_notes')) DeliveryNote::query()->delete();
        if (Schema::hasTable('origins')) Origin::query()->delete();

        Schema::enableForeignKeyConstraints();
    }
}