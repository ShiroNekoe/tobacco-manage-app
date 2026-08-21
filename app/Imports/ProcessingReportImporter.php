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

            // ===== PARSE BOTH TOP HEADER TABLES (DELIVERY NOTE & MRL) =====
            [$dnHeaderRows, $mrlHeaderRows] = $this->parseHeaderTables($sheet);

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
                        'raw_origin' => $rawOrigin,
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

                // PARSE SEPARATION SUMMARY ROW
                if ($currentSection && $prodQtyCol && $bitsCol && $dustCol && $wasteCol) {
                    $pVal = (float) $sheet->getCell([$prodQtyCol, $r])->getCalculatedValue();
                    $bVal = (float) $sheet->getCell([$bitsCol, $r])->getCalculatedValue();
                    $dVal = (float) $sheet->getCell([$dustCol, $r])->getCalculatedValue();
                    $wVal = (float) $sheet->getCell([$wasteCol, $r])->getCalculatedValue();
                    $totVal = $totalQtyCol ? (float) $sheet->getCell([$totalQtyCol, $r])->getCalculatedValue() : ($pVal + $bVal + $dVal + $wVal);

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
            $dnHeaderDetailsArray = [];
            $dnTotalPackSum = 0;
            $dnGrossWeightSum = 0;
            $dnTareWeightSum = 0;
            $dnNettoWeightSum = 0;

            if (!empty($dnHeaderRows)) {
                $firstOrigin = $dnHeaderRows[0]['origin_obj'];
                $firstPackType = $dnHeaderRows[0]['pack_type'];
                $firstMaterialCode = $dnHeaderRows[0]['material_code'];

                foreach ($dnHeaderRows as $hOrig) {
                    $dnHeaderDetailsArray[] = [
                        'product_type' => 'RAJANGAN',
                        'raw_origin' => $hOrig['raw_origin'],
                        'clean_region' => $hOrig['clean_region'],
                        'material_code' => $hOrig['material_code'],
                        'packs' => (int) $hOrig['packs'],
                        'pack_type' => $hOrig['pack_type'],
                        'gross_kg' => round($hOrig['gross_kg'], 2),
                        'tare_kg' => round($hOrig['tare_kg'], 2),
                        'netto_kg' => round($hOrig['netto_kg'], 2),
                        'dn_number' => $hOrig['dn_number'],
                    ];
                    $dnTotalPackSum += (int) $hOrig['packs'];
                    $dnGrossWeightSum += round($hOrig['gross_kg'], 2);
                    $dnTareWeightSum += round($hOrig['tare_kg'], 2);
                    $dnNettoWeightSum += round($hOrig['netto_kg'], 2);
                }
            } else {
                $firstOrigin = !empty($parsedSections) ? $parsedSections[0]['origin'] : Origin::first();
                $firstPackType = !empty($parsedSections) ? $parsedSections[0]['pack_type'] : 'Bale';
                $firstMaterialCode = !empty($parsedSections) ? $parsedSections[0]['material_code'] : 'N/A';
            }

            $mrlHeaderDetailsArray = [];
            $mrlTotalPackSum = 0;
            $mrlGrossWeightSum = 0;
            $mrlTareWeightSum = 0;
            $mrlNettoWeightSum = 0;
            $mrlDiscrepancySum = 0;

            if (!empty($mrlHeaderRows)) {
                foreach ($mrlHeaderRows as $mOrig) {
                    $mrlHeaderDetailsArray[] = [
                        'product_type' => 'RAJANGAN',
                        'raw_origin' => $mOrig['raw_origin'],
                        'clean_region' => $mOrig['clean_region'],
                        'material_code' => $mOrig['material_code'],
                        'packs' => (int) $mOrig['packs'],
                        'pack_type' => $mOrig['pack_type'],
                        'gross_kg' => round($mOrig['gross_kg'], 2),
                        'tare_kg' => round($mOrig['tare_kg'], 2),
                        'netto_kg' => round($mOrig['netto_kg'], 2),
                        'discrepancy_kg' => round($mOrig['discrepancy_kg'], 2),
                    ];
                    $mrlTotalPackSum += (int) $mOrig['packs'];
                    $mrlGrossWeightSum += round($mOrig['gross_kg'], 2);
                    $mrlTareWeightSum += round($mOrig['tare_kg'], 2);
                    $mrlNettoWeightSum += round($mOrig['netto_kg'], 2);
                    $mrlDiscrepancySum += round($mOrig['discrepancy_kg'], 2);
                }
            } else {
                $mrlHeaderDetailsArray = $dnHeaderDetailsArray;
                $mrlTotalPackSum = $dnTotalPackSum;
                $mrlGrossWeightSum = $dnGrossWeightSum;
                $mrlTareWeightSum = $dnTareWeightSum;
                $mrlNettoWeightSum = $dnNettoWeightSum;
            }

            $sectionsDataArray = [];
            foreach ($parsedSections as $sec) {
                $sectionsDataArray[] = [
                    'raw_origin' => $sec['raw_origin'] ?? ($sec['origin_name'] ?? 'Material'),
                    'clean_region' => $sec['origin_name'] ?? ($sec['clean_region'] ?? 'TEMANGGUNG'),
                    'material_code' => $sec['material_code'] ?? 'DEFAULT',
                    'pack_type' => $sec['pack_type'] ?? 'Bale',
                    'sacks' => $sec['sacks'] ?? [],
                    'separation' => $sec['separation'] ?? null,
                ];
            }

            $batch = Batch::create([
                'batch_code' => $batchCode,
                'customer_id' => $customer->id,
                'delivery_note_id' => $dn->id,
                'product_type_id' => $defaultProductType->id,
                'origin_id' => $firstOrigin->id,
                'material_code' => $firstMaterialCode,
                'dn_header_details' => $dnHeaderDetailsArray,
                'mrl_header_details' => $mrlHeaderDetailsArray,
                'sections_data' => $sectionsDataArray,
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

            // Sync BatchOrigin from Header Origins if available (accurate multi-origin per header table)
            if (!empty($dnHeaderRows)) {
                BatchOrigin::where('batch_id', $batch->id)->delete();
                foreach ($dnHeaderRows as $hOrig) {
                    BatchOrigin::create([
                        'batch_id' => $batch->id,
                        'origin_id' => $hOrig['origin_obj']->id,
                        'allocated_kg' => round($hOrig['gross_kg'], 2),
                        'remaining_kg' => 0,
                        'status' => 'completed',
                    ]);
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

            $finalDnPack = $dnTotalPackSum > 0 ? $dnTotalPackSum : $secSackCount;
            $finalDnGross = $dnGrossWeightSum > 0 ? $dnGrossWeightSum : round($secGross, 2);
            $finalDnTare = $dnTareWeightSum > 0 ? $dnTareWeightSum : round($secTare, 2);
            $finalDnNetto = $dnNettoWeightSum > 0 ? $dnNettoWeightSum : round($secNetto, 2);

            $finalMrlPack = $mrlTotalPackSum > 0 ? $mrlTotalPackSum : $finalDnPack;
            $finalMrlGross = $mrlGrossWeightSum > 0 ? $mrlGrossWeightSum : $finalDnGross;
            $finalMrlTare = $mrlTareWeightSum > 0 ? $mrlTareWeightSum : $finalDnTare;
            $finalMrlNetto = $mrlNettoWeightSum > 0 ? $mrlNettoWeightSum : $finalDnNetto;
            $finalDiscrepancy = round($finalMrlGross - $finalDnGross, 2);

            // Update Batch totals
            $batch->update([
                'dn_total_pack' => $finalDnPack,
                'dn_gross_weight' => $finalDnGross,
                'dn_tare_weight' => $finalDnTare,
                'dn_netto_weight' => $finalDnNetto,
                'mrl_total_pack' => $finalMrlPack,
                'mrl_gross_weight' => $finalMrlGross,
                'mrl_tare_weight' => $finalMrlTare,
                'mrl_netto_weight' => $finalMrlNetto,
                'discrepancy_dn_vs_mrl_kg' => $finalDiscrepancy,
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
        $this->seedSampleDnShipments($spreadsheet);

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
     * Parse both top header tables (DELIVERY NOTE and MATERIAL RECEIPT LIST) for a batch sheet
     */
    protected function parseHeaderTables($sheet): array
    {
        $highestRow = $sheet->getHighestRow();

        $dnStart = null;
        $mrlStart = null;
        $sepStart = null;

        for ($r = 1; $r <= $highestRow; $r++) {
            $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
            if (! $dnStart && (preg_match('/DELIVERY NOTE\s*\(DN\)/i', $c1) || preg_match('/DELIVERY NOTE\b/i', $c1))) {
                $dnStart = $r;
            }
            if (! $mrlStart && (preg_match('/MATERIAL RECEIPT LIST\s*\(MRL\)/i', $c1) || preg_match('/MATERIAL RECEIPT LIST\b/i', $c1))) {
                $mrlStart = $r;
            }
            if (! $sepStart && preg_match('/SEPARATION RESULTS REPORT/i', $c1)) {
                $sepStart = $r;
            }
        }

        $dnRows = [];
        $mrlRows = [];

        // Parse DN Table
        if ($dnStart) {
            $dnEnd = $mrlStart ?: ($sepStart ?: $highestRow);
            for ($r = $dnStart; $r < $dnEnd; $r++) {
                $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
                $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());
                if (empty($c1) || str_contains(strtolower($c1), 'product type') || str_contains(strtolower($c2), 'origin') || str_contains(strtolower($c1), 'customer') || str_contains(strtolower($c1), 'remark') || str_contains(strtolower($c1), 'delivery note')) {
                    continue;
                }
                $packs = (float) $sheet->getCell([3, $r])->getCalculatedValue();
                $packType = trim((string) $sheet->getCell([4, $r])->getCalculatedValue());
                $gross = (float) $sheet->getCell([5, $r])->getCalculatedValue();
                $tare = (float) $sheet->getCell([6, $r])->getCalculatedValue();
                $netto = (float) $sheet->getCell([7, $r])->getCalculatedValue();
                $dnNum = trim((string) $sheet->getCell([9, $r])->getCalculatedValue());

                if ($packs == 0 && is_numeric($packType) && (float) $packType > 0) {
                    $packs = (float) $packType;
                    $packType = 'Bale';
                }

                if ($gross > 0 || $packs > 0) {
                    [$cleanRegion, $materialCode] = $this->parseOriginAndCode($c2);
                    $originObj = Origin::firstOrCreate(['region_name' => $cleanRegion]);

                    $dnRows[] = [
                        'product_type' => 'RAJANGAN',
                        'raw_origin' => $c2,
                        'clean_region' => $cleanRegion,
                        'material_code' => $materialCode,
                        'origin_obj' => $originObj,
                        'packs' => (int) $packs,
                        'pack_type' => (! empty($packType) && ! is_numeric($packType)) ? $packType : 'Bale',
                        'gross_kg' => round($gross, 2),
                        'tare_kg' => round($tare, 2),
                        'netto_kg' => round($netto, 2),
                        'dn_number' => $dnNum,
                    ];
                }
            }
        }

        // Parse MRL Table
        if ($mrlStart) {
            $mrlEnd = $sepStart ?: $highestRow;
            for ($r = $mrlStart; $r < $mrlEnd; $r++) {
                $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
                $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());
                if (empty($c1) || str_contains(strtolower($c1), 'product type') || str_contains(strtolower($c2), 'origin') || str_contains(strtolower($c1), 'customer') || str_contains(strtolower($c1), 'remark') || str_contains(strtolower($c1), 'material receipt')) {
                    continue;
                }
                $packs = (float) $sheet->getCell([3, $r])->getCalculatedValue();
                $packType = trim((string) $sheet->getCell([4, $r])->getCalculatedValue());
                $gross = (float) $sheet->getCell([5, $r])->getCalculatedValue();
                $tare = (float) $sheet->getCell([6, $r])->getCalculatedValue();
                $netto = (float) $sheet->getCell([7, $r])->getCalculatedValue();
                $discrepancy = (float) $sheet->getCell([9, $r])->getCalculatedValue();

                if ($packs == 0 && is_numeric($packType) && (float) $packType > 0) {
                    $packs = (float) $packType;
                    $packType = 'Bale';
                }

                if ($gross > 0 || $packs > 0) {
                    [$cleanRegion, $materialCode] = $this->parseOriginAndCode($c2);
                    $originObj = Origin::firstOrCreate(['region_name' => $cleanRegion]);

                    $mrlRows[] = [
                        'product_type' => 'RAJANGAN',
                        'raw_origin' => $c2,
                        'clean_region' => $cleanRegion,
                        'material_code' => $materialCode,
                        'origin_obj' => $originObj,
                        'packs' => (int) $packs,
                        'pack_type' => (! empty($packType) && ! is_numeric($packType)) ? $packType : 'Bale',
                        'gross_kg' => round($gross, 2),
                        'tare_kg' => round($tare, 2),
                        'netto_kg' => round($netto, 2),
                        'discrepancy_kg' => round($discrepancy, 2),
                    ];
                }
            }
        }

        if (empty($mrlRows)) {
            $mrlRows = array_map(function ($row) {
                $row['discrepancy_kg'] = 0.00;
                unset($row['dn_number']);
                return $row;
            }, $dnRows);
        }

        if (empty($dnRows)) {
            $dnRows = array_map(function ($row) {
                $row['dn_number'] = '-';
                unset($row['discrepancy_kg']);
                return $row;
            }, $mrlRows);
        }

        return [$dnRows, $mrlRows];
    }

    /**
     * Seed sample Outbound DN Shipments for testing/demo covering all batches 1..25
     */
    protected function seedSampleDnShipments(?\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet = null): void
    {
        if (DnShipment::count() > 0) {
            return;
        }

        if (! $spreadsheet) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($this->filePath);
        }

        $customer = Customer::where('name', 'like', '%Falih%')->first() ?? Customer::first();
        $productType = ProductType::where('code', 'RAJANGAN')->first() ?? ProductType::first();

        $batches = Batch::with(['origin', 'batchOrigins.origin'])->orderBy('id')->get();
        if ($batches->isEmpty()) {
            return;
        }

        $drivers = ['Bambang Haryanto', 'Sugiarto', 'Rudi Hermawan', 'Eko Prasetyo', 'Agus Susanto', 'Dwi Cahyono', 'Heri Setiawan'];
        $vehicles = ['B 9482 FNG', 'N 8102 UC', 'L 9204 AB', 'P 8192 XY', 'B 9102 CD', 'W 8091 QR', 'DK 9102 FG'];
        $destinations = [
            'Gudang Utama Malang, Jawa Timur',
            'Pabrik Cigarette Pasuruan',
            'Depo Logistik Surabaya',
            'Gudang Transit Jember',
            'Pabrik Pengolahan Kudus',
        ];
        $statuses = ['Approved', 'Approved', 'Shipped', 'Approved', 'Approved', 'Shipped', 'Approved'];

        $chunks = $batches->chunk(2);
        $shipmentIndex = 1;

        foreach ($chunks as $chunk) {
            $shipmentNo = 'DN-OUT-2026-' . str_pad($shipmentIndex, 4, '0', STR_PAD_LEFT);
            $shipmentDate = Carbon::now()->subDays(60 - ($shipmentIndex * 4));
            $driver = $drivers[($shipmentIndex - 1) % count($drivers)];
            $vehicle = $vehicles[($shipmentIndex - 1) % count($vehicles)];
            $dest = $destinations[($shipmentIndex - 1) % count($destinations)];
            $status = $statuses[($shipmentIndex - 1) % count($statuses)];

            $shipment = DnShipment::create([
                'dn_number' => $shipmentNo,
                'shipment_date' => $shipmentDate,
                'customer_id' => $customer?->id,
                'product_type_id' => $productType?->id,
                'vehicle_number' => $vehicle,
                'driver_name' => $driver,
                'destination' => $dest,
                'notes' => "Pengiriman Outbound Hasil Olahan Tembakau (Kloter {$shipmentIndex})",
                'total_sacks' => 0,
                'total_gross_kg' => 0,
                'total_tare_kg' => 0,
                'total_netto_kg' => 0,
                'status' => $status,
                'customer_approved_at' => $status === 'Approved' ? $shipmentDate->copy()->addDay() : null,
                'customer_approval_note' => $status === 'Approved' ? 'Diterima lengkap & sesuai kuantitas.' : null,
            ]);

            $itemNo = 1;
            foreach ($chunk as $batch) {
                // Find matching sheet for this batch to get exact header table origins
                $matchingSheet = null;
                $batchNum = (int) str_replace('BCH-2026-', '', $batch->batch_code);
                foreach ($spreadsheet->getSheetNames() as $sheetName) {
                    if (preg_match('/SPR\s*Batch\s*' . $batchNum . '$/i', trim($sheetName))) {
                        $matchingSheet = $spreadsheet->getSheetByName($sheetName);
                        break;
                    }
                }

                [$hOrigins, $mrlOrigins] = $matchingSheet ? $this->parseHeaderTables($matchingSheet) : [[], []];

                if (!empty($hOrigins)) {
                    foreach ($hOrigins as $hOrig) {
                        $packs = max(1, $hOrig['packs']);
                        $gross = $hOrig['gross_kg'] > 0 ? $hOrig['gross_kg'] : round($packs * 50.20, 2);
                        $tare = $hOrig['tare_kg'] > 0 ? $hOrig['tare_kg'] : round($packs * 0.20, 2);
                        $netto = max(0, round($gross - $tare, 2));

                        DnShipmentItem::create([
                            'dn_shipment_id' => $shipment->id,
                            'batch_id' => $batch->id,
                            'batch_code' => $batch->batch_code,
                            'item_no' => $itemNo++,
                            'origin' => $hOrig['clean_region'],
                            'origin_code' => $hOrig['material_code'],
                            'material_type' => 'Product',
                            'standard_sack_count' => $packs,
                            'standard_gross_per_sack' => round($gross / $packs, 2),
                            'standard_tare_per_sack' => round($tare / $packs, 2),
                            'standard_netto_per_sack' => round($netto / $packs, 2),
                            'has_remnant' => false,
                            'remnant_gross_kg' => 0.00,
                            'remnant_tare_kg' => 0.00,
                            'remnant_netto_kg' => 0.00,
                            'total_sacks' => $packs,
                            'total_gross_kg' => $gross,
                            'total_tare_kg' => $tare,
                            'total_netto_kg' => $netto,
                        ]);
                    }
                } else {
                    $originName = $batch->origin->region_name ?? 'KASTURI';
                    $originCode = !empty($batch->material_code) ? $batch->material_code : '-';
                    $tarePerSack = (float) ($batch->product_tare_per_sack ?? 0.20);
                    $grossPerSack = (float) ($batch->product_kg_per_sack ?: 50.20);

                    $totalSacks = max(1, (int) ($batch->dn_total_pack ?: 10));
                    $totalGross = (float) ($batch->dn_gross_weight ?: ($totalSacks * $grossPerSack));
                    $totalTare = round($totalSacks * $tarePerSack, 2);
                    $totalNetto = max(0, round($totalGross - $totalTare, 2));

                    DnShipmentItem::create([
                        'dn_shipment_id' => $shipment->id,
                        'batch_id' => $batch->id,
                        'batch_code' => $batch->batch_code,
                        'item_no' => $itemNo++,
                        'origin' => $originName,
                        'origin_code' => $originCode,
                        'material_type' => 'Product',
                        'standard_sack_count' => $totalSacks,
                        'standard_gross_per_sack' => round($totalGross / $totalSacks, 2),
                        'standard_tare_per_sack' => $tarePerSack,
                        'standard_netto_per_sack' => round($totalNetto / $totalSacks, 2),
                        'has_remnant' => false,
                        'remnant_gross_kg' => 0.00,
                        'remnant_tare_kg' => 0.00,
                        'remnant_netto_kg' => 0.00,
                        'total_sacks' => $totalSacks,
                        'total_gross_kg' => $totalGross,
                        'total_tare_kg' => $totalTare,
                        'total_netto_kg' => $totalNetto,
                    ]);
                }
            }

            $shipment->recalculateTotals();
            $shipmentIndex++;
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