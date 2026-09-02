<?php

require __DIR__ . '/../vendor/autoload.php';

$filePath = __DIR__ . '/../app/Imports/Processing Report_DAVEN.xlsx';
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/../app/Imports/Processing_Report_DAVEN.xlsx';
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($filePath);

foreach ($spreadsheet->getSheetNames() as $sName) {
    if (preg_match('/Batch\s*3/i', $sName)) {
        $sheet = $spreadsheet->getSheetByName($sName);
        break;
    }
}

echo "=== RAW DN & MRL HEADER TABLES IN BATCH 3 (Rows 1 to 20) ===\n";
for ($r = 1; $r <= 20; $r++) {
    $rowVals = [];
    for ($c = 1; $c <= 10; $c++) {
        $val = $sheet->getCell([$c, $r])->getCalculatedValue();
        $rowVals[] = "C{$c}=" . var_export($val, true);
    }
    echo sprintf("Row %02d: %s\n", $r, implode(' | ', $rowVals));
}
