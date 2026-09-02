<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================================\n";
echo "VERIFYING PDF RENDERING FOR ALL 25 BATCHES IN MYSQL DB\n";
echo "========================================================\n\n";

for ($bNum = 1; $bNum <= 25; $bNum++) {
    $code = 'BCH-2026-' . str_pad($bNum, 4, '0', STR_PAD_LEFT);
    $batch = App\Models\Batch::where('batch_code', $code)->first();
    if (!$batch) {
        echo "Batch {$code}: NOT FOUND in MySQL DB!\n";
        continue;
    }

    $html = view('certificates.process-certificate-pdf', ['batch' => $batch])->render();

    // Extract DN Table rows count
    preg_match('/DELIVERY NOTE \(DN\) :<\/div>\s*<table class="pdf-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $dnMatch);
    $dnRowsHtml = $dnMatch[1] ?? '';
    preg_match_all('/<tr>/', $dnRowsHtml, $dnTrMatches);
    $dnRowCount = count($dnTrMatches[0] ?? []);

    // Extract MRL Table rows count
    preg_match('/MATERIAL RECEIPT LIST \(MRL\) :<\/div>\s*<table class="pdf-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $mrlMatch);
    $mrlRowsHtml = $mrlMatch[1] ?? '';
    preg_match_all('/<tr>/', $mrlRowsHtml, $mrlTrMatches);
    $mrlRowCount = count($mrlTrMatches[0] ?? []);

    // Extract Issued Date
    preg_match('/Issued Date :<\/td>\s*<td[^>]*>(.*?)<\/td>/s', $html, $dateMatch);
    $issuedDateHtml = trim(strip_tags($dateMatch[1] ?? 'N/A'));

    $secCount = count($batch->sections_data ?? []);

    echo sprintf(
        "Batch %02d (%s) | Issued Date: %-10s | PDF DN Rows: %d | PDF MRL Rows: %d | Separation Sections: %d\n",
        $bNum,
        $code,
        $issuedDateHtml,
        $dnRowCount,
        $mrlRowCount,
        $secCount
    );

    if ($dnRowCount !== $secCount || $mrlRowCount !== $secCount) {
        echo "   [ERROR] MISMATCH DETECTED IN PDF FOR BATCH {$code}!\n";
    }
}
