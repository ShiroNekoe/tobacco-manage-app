<?php

namespace App\Console\Commands;

use App\Imports\ProcessingReportImporter;
use Illuminate\Console\Command;

class ImportProcessingReportsCommand extends Command
{
    protected $signature = 'import:processing-reports {--reset : Truncate transaction tables before import} {--path= : Custom path to Excel file}';

    protected $description = 'Import 100% of historical production data from Excel file (Batch 1 to 25)';

    public function handle(): int
    {
        $this->info('🚀 Starting TPMS Excel Data Import (Batch 1 to 25)...');

        $reset = $this->option('reset');
        $path = $this->option('path');

        if ($reset) {
            $this->warn('⚠️ --reset flag passed! Truncating transactional tables (batches, weighing_items, etc)...');
        }

        try {
            $importer = new ProcessingReportImporter($path);
            $result = $importer->import($reset);

            $this->newLine();
            $this->info('===================================================');
            $this->info('   ✅ EXCEL IMPORT COMPLETED SUCCESSFULLY!');
            $this->info('===================================================');
            $this->table(
                ['Metric', 'Imported Count'],
                [
                    ['Batches Imported', $result['batches']],
                    ['Origin Sessions Processed', $result['origins']],
                    ['Sack Weighing Items Imported', $result['sacks']],
                    ['Separation Result Summaries', $result['separations']],
                ]
            );
            $this->newLine();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ IMPORT ERROR: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
