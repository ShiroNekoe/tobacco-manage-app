<?php

require __DIR__ . '/../vendor/autoload.php';

$filePath = __DIR__ . '/../app/Imports/Processing Report_DAVEN.xlsx';
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/../app/Imports/Processing_Report_DAVEN.xlsx';
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($filePath);

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    if (!preg_match('/SPR\s*Batch\s*(\d+)/i', trim($sheetName), $matches)) {
        continue;
    }
    $batchNum = (int) $matches[1];
    $sheet = $spreadsheet->getSheetByName($sheetName);
    
    // Look for Issued Date in top rows
    $issuedDateRaw = null;
    $issuedDateFormatted = null;
    
    for ($r = 1; $r <= 10; $r++) {
        for ($c = 1; $c <= 10; $c++) {
            $val = trim((string) $sheet->getCell([$c, $r])->getCalculatedValue());
            if (str_contains(strtolower($val), 'issued date')) {
                $nextVal = $sheet->getCell([$c + 1, $r])->getCalculatedValue();
                if ($nextVal === null || $nextVal === '') {
                    $nextVal = $sheet->getCell([$c + 2, $r])->getCalculatedValue();
                }
                $issuedDateRaw = $nextVal;
                if (is_numeric($nextVal)) {
                    $issuedDateFormatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($nextVal)->format('Y-m-d (d/m/Y)');
                } else {
                    $issuedDateFormatted = (string) $nextVal;
                }
                break 2;
            }
        }
    }
    
    // Look for Date of Receipt in row 7 or table
    $receiptDateRaw = null;
    $receiptDateFormatted = null;
    for ($r = 6; $r <= 12; $r++) {
        $val = $sheet->getCell([7, $r])->getCalculatedValue(); // Col 7 (G)
        if (is_numeric($val) && $val > 40000) {
            $receiptDateRaw = $val;
            $receiptDateFormatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d (d/m/Y)');
            break;
        }
    }

    echo sprintf("Batch %02d | Issued Date: %-25s | Date of Receipt: %s\n", $batchNum, $issuedDateFormatted ?? 'N/A', $receiptDateFormatted ?? 'N/A');
}
