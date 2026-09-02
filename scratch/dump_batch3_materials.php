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
$highestRow = $sheet->getHighestRow();

echo "=== SEPARATION RESULTS SECTIONS IN BATCH 3 ===\n";
for ($r = 1; $r <= $highestRow; $r++) {
    $c1 = trim((string) $sheet->getCell([1, $r])->getCalculatedValue());
    if (preg_match('/Material\s*Desc/i', $c1)) {
        $c2 = trim((string) $sheet->getCell([2, $r])->getCalculatedValue());
        echo "Row {$r}: {$c1} {$c2}\n";
    }
}
