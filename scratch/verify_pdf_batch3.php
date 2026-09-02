<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batch3 = App\Models\Batch::where('batch_code', 'BCH-2026-0003')->first();
if (!$batch3) {
    echo "Batch 3 not found!\n";
    exit;
}

$html = view('certificates.process-certificate-pdf', ['batch' => $batch3])->render();

// Extract DELIVERY NOTE table rows
preg_match('/DELIVERY NOTE \(DN\) :<\/div>\s*<table class="pdf-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $dnMatch);
$dnRowsHtml = $dnMatch[1] ?? '';
preg_match_all('/<tr>/', $dnRowsHtml, $dnTrMatches);
$dnRowCount = count($dnTrMatches[0] ?? []);

// Extract MATERIAL RECEIPT LIST table rows
preg_match('/MATERIAL RECEIPT LIST \(MRL\) :<\/div>\s*<table class="pdf-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $mrlMatch);
$mrlRowsHtml = $mrlMatch[1] ?? '';
preg_match_all('/<tr>/', $mrlRowsHtml, $mrlTrMatches);
$mrlRowCount = count($mrlTrMatches[0] ?? []);

// Extract Issued Date from HTML
preg_match('/Issued Date :<\/td>\s*<td[^>]*>(.*?)<\/td>/s', $html, $dateMatch);
$issuedDateHtml = trim(strip_tags($dateMatch[1] ?? 'N/A'));

echo "========================================================\n";
echo "PDF VERIFICATION FOR BATCH 3 (BCH-2026-0003)\n";
echo "========================================================\n";
echo "Issued Date in PDF Header: {$issuedDateHtml}\n";
echo "Delivery Note (DN) Table Rows in PDF: {$dnRowCount}\n";
echo "Material Receipt List (MRL) Table Rows in PDF: {$mrlRowCount}\n\n";

echo "DN Table HTML Snippet:\n" . trim(strip_tags($dnRowsHtml)) . "\n";
