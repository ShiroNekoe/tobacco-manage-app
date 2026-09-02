<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = base_path('app/Imports/Processing Report_DAVEN.xlsx');
if (!file_exists($filePath)) {
    $filePath = base_path('app/Imports/Processing_Report_DAVEN.xlsx');
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($filePath);

echo "========================================================\n";
echo "AUDITING ALL 25 BATCH SHEETS IN EXCEL FILE\n";
echo "========================================================\n\n";

for ($b = 1; $b <= 25; $b++) {
    $sheetName = "SPR Batch {$b}";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    if (!$sheet) {
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (preg_match('/Batch\s*' . $b . '$/i', trim($name))) {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
    }

    if (!$sheet) {
        echo "Batch {$b}: Sheet not found!\n";
        continue;
    }

    $highestRow = $sheet->getHighestRow();

    // 1. Issued Date
    $issuedDate = null;
    for ($r = 1; $r <= 10; $r++) {
        for ($c = 1; $c <= 10; $c++) {
            $val = trim((string) $sheet->getCell([$c, $r])->getCalculatedValue());
            if (str_contains(strtolower($val), 'issued date')) {
                $nextVal = $sheet->getCell([$c + 1, $r])->getCalculatedValue() ?: $sheet->getCell([$c + 2, $r])->getCalculatedValue();
                if (is_numeric($nextVal)) {
                    $issuedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($nextVal)->format('Y-m-d');
                } else {
                    $issuedDate = (string) $nextVal;
                }
                break 2;
            }
        }
    }

    // 2. Count DN Header table rows (non-empty rows between DELIVERY NOTE and MATERIAL RECEIPT LIST)
    $dnStart = null;
    $mrlStart = null;
    $sepStart = null;
    for ($r = 1; $r <= $highestRow; $r++) {
        $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
        if (!$dnStart && preg_match('/DELIVERY NOTE/i', $c1)) $dnStart = $r;
        if (!$mrlStart && preg_match('/MATERIAL RECEIPT LIST/i', $c1)) $mrlStart = $r;
        if (!$sepStart && preg_match('/SEPARATION RESULTS REPORT/i', $c1)) $sepStart = $r;
    }

    $excelDnHeaderRows = [];
    if ($dnStart) {
        $dnEnd = $mrlStart ?: ($sepStart ?: $highestRow);
        for ($r = $dnStart; $r < $dnEnd; $r++) {
            $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
            $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());
            $c3 = trim((string) $sheet->getCell([3, $r])->getCalculatedValue());
            $c4 = (float) $sheet->getCell([4, $r])->getCalculatedValue();
            $c5 = (float) $sheet->getCell([5, $r])->getCalculatedValue();
            $c6 = (float) $sheet->getCell([6, $r])->getCalculatedValue();
            if (empty($c1) || str_contains(strtolower($c1), 'product type') || str_contains(strtolower($c2), 'origin') || str_contains(strtolower($c1), 'customer') || str_contains(strtolower($c1), 'remark') || str_contains(strtolower($c1), 'delivery note')) {
                continue;
            }
            if ($c4 > 0 || $c5 > 0 || $c6 > 0) {
                $excelDnHeaderRows[] = [
                    'row' => $r,
                    'prod_type' => $c1,
                    'origin' => $c2,
                    'pack_type' => $c3,
                    'gross' => $c4,
                    'tare' => $c5,
                    'netto' => $c6,
                ];
            }
        }
    }

    // 3. Count Separation Material Desc Sections
    $excelSeparationSections = [];
    for ($r = 1; $r <= $highestRow; $r++) {
        $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
        if (preg_match('/Material\s*Desc/i', $c1)) {
            $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());
            $excelSeparationSections[] = [
                'row' => $r,
                'desc' => "{$c1} {$c2}",
            ];
        }
    }

    echo sprintf(
        "Batch %02d | Sheet: %-15s | Issued Date: %-10s | Excel DN Rows: %d | Excel Separation Sections: %d\n",
        $b,
        $sheetName,
        $issuedDate ?? 'N/A',
        count($excelDnHeaderRows),
        count($excelSeparationSections)
    );

    if (count($excelDnHeaderRows) !== count($excelSeparationSections)) {
        echo "   --> TEMPLATE DIFFERENCE IN EXCEL: Header has " . count($excelDnHeaderRows) . " rows, but Separation has " . count($excelSeparationSections) . " sections.\n";
        foreach ($excelDnHeaderRows as $h) {
            echo "       Header Row {$h['row']}: {$h['origin']} | {$h['pack_type']} | Gross: {$h['gross']}\n";
        }
        foreach ($excelSeparationSections as $s) {
            echo "       Separation Section Row {$s['row']}: {$s['desc']}\n";
        }
    }
}
