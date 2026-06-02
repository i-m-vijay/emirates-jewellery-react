<?php

namespace App\Console\Commands;

use App\Services\ProductDetailImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportProductDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:product-details {file : Path to the CSV file} {--clear : Clear existing data before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import product details from CSV file into product_detail table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $clearData = $this->option('clear');

        // Validate file
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Clear existing data if requested
        if ($clearData) {
            if ($this->confirm('This will delete all existing product_detail records. Continue?')) {
                $this->info('Clearing existing data...');
                ProductDetailImportService::clearImportedData();
                $this->line('Data cleared.');
            } else {
                $this->info('Clear operation cancelled.');
            }
        }

        $this->info('Starting import process...');
        $this->newLine();

        // Start progress bar
        $this->info("Importing from: {$filePath}");

        // Perform import
        $results = ProductDetailImportService::importProductDetails($filePath);

        $this->newLine();
        $this->info('Import Completed!');
        $this->newLine();

        // Display results
        $this->line('═══════════════════════════════════');
        $this->line('Import Statistics:');
        $this->line('═══════════════════════════════════');
        $this->line("Total Records in CSV:        {$results['total_records']}");
        $this->line("Successfully Imported:       {$results['imported']}");
        $this->line("Skipped (Duplicates):        {$results['skipped_duplicates']}");
        $this->line("Failed:                      {$results['failed']}");
        $this->line('═══════════════════════════════════');

        // Display warnings
        if (!empty($results['warnings'])) {
            $this->newLine();
            $this->warn('Warnings (' . count($results['warnings']) . '):');
            foreach (array_slice($results['warnings'], 0, 10) as $warning) {
                $this->line('  ⚠ ' . $warning);
            }
            if (count($results['warnings']) > 10) {
                $this->line('  ... and ' . (count($results['warnings']) - 10) . ' more warnings');
            }
        }

        // Display errors
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Errors (' . count($results['errors']) . '):');
            foreach (array_slice($results['errors'], 0, 10) as $error) {
                $this->line('  ✗ ' . $error);
            }
            if (count($results['errors']) > 10) {
                $this->line('  ... and ' . (count($results['errors']) - 10) . ' more errors');
            }
        }

        $this->newLine();

        // Get and display statistics
        $stats = ProductDetailImportService::getImportStats();
        $this->info('Current Database Statistics:');
        $this->line("  • Total Records in Table: {$stats['total_records']}");
        $this->line("  • Imported Today: {$stats['imported_today']}");
        $this->line("  • Unique SKUs: {$stats['unique_skus']}");
        $this->line("  • Products with Diamonds: {$stats['products_with_diamonds']}");

        $this->newLine();
        $this->info('✓ Import process finished.');

        return 0;
    }
}
