<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\LegacyImport\LegacyCustomerImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LegacyImportCustomerCommand extends Command
{
    protected $signature = 'legacy:import:customer
        {--tenant= : Existing target tenant id in the new SaaS}
        {--source=legacy_customer_v1 : Legacy source key for idempotency grouping}
        {--legacy-connection=legacy : Database connection name for legacy DB}
        {--legacy-tenant-id= : Legacy exhibitions.tenant_id to import}
        {--legacy-storage-base-path= : Optional prefix for legacy storage paths (e.g. /var/legacy/storage)}';

    protected $description = 'Import real legacy exhibitions, contacts, and public tokens for the single historical customer into one existing target tenant.';

    public function handle(LegacyCustomerImporter $importer): int
    {
        $targetTenantId = (int) $this->option('tenant');
        $legacySource = (string) $this->option('source');
        $legacyConnection = (string) $this->option('legacy-connection');
        $legacyTenantOption = $this->option('legacy-tenant-id');
        $legacyStorageBasePath = $this->option('legacy-storage-base-path');

        if ($targetTenantId <= 0) {
            $this->error('Missing required option --tenant=<existing-target-tenant-id>.');

            return self::FAILURE;
        }

        if ($legacyTenantOption === null || $legacyTenantOption === '') {
            $this->error('Missing required option --legacy-tenant-id=<legacy-exhibitions-tenant-id>.');

            return self::FAILURE;
        }

        $legacyTenantId = (int) $legacyTenantOption;
        if ($legacyTenantId <= 0) {
            $this->error('Invalid --legacy-tenant-id value: must be a positive integer.');

            return self::FAILURE;
        }

        $targetTenant = Tenant::query()->find($targetTenantId);

        if ($targetTenant === null) {
            $this->error("Target tenant [{$targetTenantId}] not found. Import aborted.");

            return self::FAILURE;
        }

        if (! array_key_exists($legacyConnection, config('database.connections', []))) {
            $this->error("Legacy DB connection [{$legacyConnection}] is not configured in database.connections.");

            return self::FAILURE;
        }

        try {
            DB::connection($legacyConnection)->getPdo();
        } catch (\Throwable $e) {
            $this->error("Cannot connect to legacy DB connection [{$legacyConnection}]: {$e->getMessage()}");

            return self::FAILURE;
        }

        try {
            $summary = $importer->import(
                targetTenant: $targetTenant,
                legacySource: $legacySource,
                legacyConnection: $legacyConnection,
                legacyTenantId: $legacyTenantId,
                legacyStorageBasePath: $legacyStorageBasePath,
            );
        } catch (\Throwable $e) {
            $this->error('Legacy import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Legacy import completed.');
        $this->table(['Metric', 'Value'], [
            ['Batch ID', (string) $summary['batch_id']],
            ['Target tenant', (string) $summary['target_tenant_id']],
            ['Legacy tenant', (string) $summary['legacy_tenant_id']],
            ['Legacy connection', (string) $summary['legacy_connection']],
            ['Exhibitions created', (string) $summary['exhibitions_created']],
            ['Exhibitions updated', (string) $summary['exhibitions_updated']],
            ['Exhibitions skipped', (string) $summary['exhibitions_skipped']],
            ['Contacts created', (string) $summary['contacts_created']],
            ['Contacts updated', (string) $summary['contacts_updated']],
            ['Contacts skipped', (string) $summary['contacts_skipped']],
            ['Tokens updated', (string) $summary['tokens_updated']],
            ['Warnings', (string) $summary['warnings']],
            ['Soft-deleted policy', (string) $summary['soft_deleted_policy']],
        ]);

        if (! empty($summary['warnings_list'])) {
            foreach ($summary['warnings_list'] as $warning) {
                $this->warn((string) $warning);
            }
        }

        $this->line('Note: in this branch file assets are only referenced via stored path; no physical copy from legacy storage is performed.');

        return self::SUCCESS;
    }
}
