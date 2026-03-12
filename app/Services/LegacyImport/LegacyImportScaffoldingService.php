<?php

namespace App\Services\LegacyImport;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\ImportBatch;

class LegacyImportScaffoldingService
{
    public function startBatch(string $source, bool $dryRun = false, array $meta = []): ImportBatch
    {
        return ImportBatch::query()->create([
            'source' => $source,
            'status' => 'running',
            'dry_run' => $dryRun,
            'meta' => $meta,
            'started_at' => now(),
        ]);
    }

    public function completeBatch(ImportBatch $batch, array $summary = []): ImportBatch
    {
        $batch->forceFill([
            'status' => 'completed',
            'finished_at' => now(),
            'summary' => $summary,
        ])->save();

        return $batch->refresh();
    }

    public function failBatch(ImportBatch $batch, string $reason, array $summary = []): ImportBatch
    {
        $batch->forceFill([
            'status' => 'failed',
            'finished_at' => now(),
            'summary' => array_merge($summary, ['reason' => $reason]),
        ])->save();

        return $batch->refresh();
    }

    /**
     * Build the canonical idempotency identity used by real importers in future branches.
     */
    public function legacyIdentity(int $tenantId, string $legacySource, int|string $legacyId): array
    {
        return [
            'tenant_id' => $tenantId,
            'legacy_source' => $legacySource,
            'legacy_id' => (int) $legacyId,
        ];
    }

    public function findImportedExhibition(int $tenantId, string $legacySource, int|string $legacyId): ?Exhibition
    {
        return Exhibition::query()->where($this->legacyIdentity($tenantId, $legacySource, $legacyId))->first();
    }

    public function findImportedContact(int $tenantId, string $legacySource, int|string $legacyId): ?Contact
    {
        return Contact::query()->where($this->legacyIdentity($tenantId, $legacySource, $legacyId))->first();
    }
}
