<?php

namespace App\Console\Commands;

use App\Services\LegacyImport\LegacyImportScaffoldingService;
use Illuminate\Console\Command;

class LegacyImportScaffoldCommand extends Command
{
    protected $signature = 'legacy:import:scaffold
        {--source=legacy_v1 : Legacy source key for idempotency grouping}
        {--dry-run : Create a technical batch without importing records}';

    protected $description = 'Create a tracked import batch and initialize legacy import scaffolding.';

    public function handle(LegacyImportScaffoldingService $service): int
    {
        $source = (string) $this->option('source');
        $dryRun = (bool) $this->option('dry-run');

        $batch = $service->startBatch($source, $dryRun, [
            'command' => self::class,
        ]);

        $service->completeBatch($batch, [
            'message' => 'Scaffolding batch created. Concrete importer steps are intentionally deferred.',
        ]);

        $this->info("Import batch #{$batch->id} created for source [{$source}] (dry-run: ".($dryRun ? 'yes' : 'no').').');
        $this->line('No data import executed in this branch: this command only scaffolds batch tracking.');

        return self::SUCCESS;
    }
}
