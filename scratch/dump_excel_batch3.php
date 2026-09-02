<?php

require __DIR__ . '/../vendor/autoload.php';

$filePath = __DIR__ . '/../app/Imports/Processing Report_DAVEN.xlsx';
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/../app/Imports/Processing_Report_DAVEN.xlsx';
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('SPR Batch 3');
if (!$sheet) {
    // try finding by pattern
    foreach ($spreadsheet->getSheetNames() as $name) {
        if (preg_match('/Batch\s*3/i', $name)) {
            $sheet = $spreadsheet->getSheetByName($name);
            break;
        }
    }
}

echo "=== RAW EXCEL CONTENT FOR SPR BATCH 3 (Rows 1 to 50) ===\n";
for ($r = 1; $r <= 50; $r++) {
    $rowVals = [];
    for ($c = 1; $c <= 10; $c++) {
        $val = $sheet->getCell([$c, $r])->getCalculatedValue();
        if ($val !== null && $val !== '') {
            $rowVals[] = "C{$c}='{$val}'";
        }
    }
    if (!empty($rowVals)) {
        echo sprintf("Row %02d: %s\n", $r, implode(' | ', $rowVals));
    }
}
