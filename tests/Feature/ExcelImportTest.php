<?php

namespace Tests\Feature;

use App\Imports\ProcessingReportImporter;
use App\Models\Batch;
use App\Models\HistoricalYieldReport;
use App\Models\WeighingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_importer_processes_all_25_batches_with_data_integrity(): void
    {
        $importer = new ProcessingReportImporter();
        $result = $importer->import(true);

        // 1. Assert exactly 25 Batches exist with supervisor_approval_status === APPROVED
        $this->assertEquals(25, $result['batches']);
        $this->assertEquals(25, Batch::count());
        $this->assertEquals(25, Batch::where('supervisor_approval_status', Batch::APPROVAL_APPROVED)->count());

        // 2. Assert sack weighing rows exist across all batches
        $this->assertGreaterThanOrEqual(1500, WeighingItem::count());

        // 3. Assert historical yield summary reports imported
        $this->assertGreaterThan(0, HistoricalYieldReport::count());

        // 4. Assert PDF generation for all 25 batches renders without division-by-zero or floating-point errors
        $allBatches = Batch::all();
        foreach ($allBatches as $b) {
            $html = view('certificates.process-certificate-pdf', ['batch' => $b])->render();
            $this->assertStringContainsString('PROCESS CERTIFICATE', $html);
            $this->assertStringContainsString($b->batch_code, $html);
        }
    }
}
