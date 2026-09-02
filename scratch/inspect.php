<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Total Batches: " . App\Models\Batch::count() . "\n";

$batch = App\Models\Batch::where('batch_code', 'BCH-2026-0003')->first();
if (!$batch) {
    echo "Batch 3 not found in DB\n";
    $first = App\Models\Batch::first();
    if ($first) echo "First batch code: " . $first->batch_code . "\n";
    exit;
}

echo "=== BATCH 3 DN HEADER DETAILS (" . count($batch->dn_header_details ?? []) . " items) ===\n";
print_r($batch->dn_header_details);

echo "=== BATCH 3 MRL HEADER DETAILS (" . count($batch->mrl_header_details ?? []) . " items) ===\n";
print_r($batch->mrl_header_details);

echo "=== BATCH 3 SECTIONS DATA (" . count($batch->sections_data ?? []) . " items) ===\n";
print_r($batch->sections_data);
