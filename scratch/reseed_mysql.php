<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running ProcessingReportImporter import(true) on MySQL DB...\n";

$importer = new App\Imports\ProcessingReportImporter();
$result = $importer->import(true);

echo "Import Result: " . json_encode($result) . "\n\n";

echo "========================================================\n";
echo "MYSQL DB AUDIT AFTER FRESH IMPORT\n";
echo "========================================================\n";

for ($bNum = 1; $bNum <= 25; $bNum++) {
    $code = 'BCH-2026-' . str_pad($bNum, 4, '0', STR_PAD_LEFT);
    $batch = App\Models\Batch::where('batch_code', $code)->first();
    if (!$batch) {
        echo "Batch {$code} NOT FOUND in MySQL DB!\n";
        continue;
    }
    
    $dnCount = count($batch->dn_header_details ?? []);
    $mrlCount = count($batch->mrl_header_details ?? []);
    $secCount = count($batch->sections_data ?? []);
    $dateReceipt = $batch->date_of_receipt ? $batch->date_of_receipt->format('Y-m-d') : 'N/A';
    $lockedAt = $batch->locked_at ? $batch->locked_at->format('Y-m-d H:i') : 'N/A';
    
    echo sprintf(
        "Batch %02d (%s) | DN Header Items: %d | MRL Items: %d | Sections: %d | Receipt Date: %s | Locked At: %s\n",
        $bNum,
        $code,
        $dnCount,
        $mrlCount,
        $secCount,
        $dateReceipt,
        $lockedAt
    );
    
    if ($dnCount !== $secCount) {
        echo "   [WARNING] Mismatch in Batch {$code}: DN Header has {$dnCount} items, Sections has {$secCount} items.\n";
    }
}
