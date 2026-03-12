<?php

namespace App\Services\LegacyImport;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyCustomerImporter
{
    public function __construct(
        private readonly LegacyImportScaffoldingService $batchService,
    ) {}

    /**
     * @return array{
     *   batch_id:int,
     *   exhibitions_created:int,
     *   exhibitions_updated:int,
     *   exhibitions_skipped:int,
     *   contacts_created:int,
     *   contacts_updated:int,
     *   contacts_skipped:int,
     *   tokens_updated:int,
     *   legacy_exhibitions_seen:int,
     *   legacy_contacts_seen:int,
     *   warnings:int,
     *   target_tenant_id:int,
     *   legacy_tenant_id:int,
     *   legacy_connection:string,
     *   soft_deleted_policy:string,
     *   warnings_list:list<string>
     * }
     */
    public function import(
        Tenant $targetTenant,
        string $legacySource,
        string $legacyConnection,
        int $legacyTenantId,
        ?string $legacyStorageBasePath = null,
    ): array {
        $batch = $this->batchService->startBatch($legacySource, false, [
            'target_tenant_id' => $targetTenant->id,
            'legacy_connection' => $legacyConnection,
            'legacy_tenant_id' => $legacyTenantId,
            'legacy_storage_base_path' => $legacyStorageBasePath,
            'soft_deleted_policy' => 'exclude_deleted_at_not_null',
        ]);

        $stats = [
            'exhibitions_created' => 0,
            'exhibitions_updated' => 0,
            'exhibitions_skipped' => 0,
            'contacts_created' => 0,
            'contacts_updated' => 0,
            'contacts_skipped' => 0,
            'tokens_updated' => 0,
            'legacy_exhibitions_seen' => 0,
            'legacy_contacts_seen' => 0,
            'warnings' => 0,
        ];

        $warnings = [];

        try {
            $exhibitionMap = [];
            $legacyExhibitions = DB::connection($legacyConnection)
                ->table('exhibitions')
                ->where('tenant_id', $legacyTenantId)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($legacyExhibitions as $legacyExhibition) {
                $stats['legacy_exhibitions_seen']++;

                $identity = $this->batchService->legacyIdentity($targetTenant->id, $legacySource, (int) $legacyExhibition->id);
                $existing = Exhibition::query()->where($identity)->first();

                $businessPayload = [
                    'tenant_id' => $targetTenant->id,
                    'name' => (string) $legacyExhibition->name,
                    'date' => $this->normalizeDateValue($legacyExhibition->date ?? null),
                    'start_date' => $this->normalizeDateValue($legacyExhibition->start_date ?? null),
                    'end_date' => $this->normalizeDateValue($legacyExhibition->end_date ?? null),
                    'company' => $legacyExhibition->company,
                    'note' => $legacyExhibition->note,
                    'legacy_source' => $legacySource,
                    'legacy_id' => (int) $legacyExhibition->id,
                    'legacy_updated_at' => $legacyExhibition->updated_at,
                ];

                if ($existing === null) {
                    $newExhibition = Exhibition::query()->create([
                        ...$businessPayload,
                        'import_batch_id' => $batch->id,
                    ]);

                    $stats['exhibitions_created']++;
                    $exhibitionMap[(int) $legacyExhibition->id] = $newExhibition->id;

                    continue;
                }

                if ($this->hasDirtyBusinessChanges($existing, $businessPayload)) {
                    $existing->fill($businessPayload);
                    $existing->save();
                    $stats['exhibitions_updated']++;
                } else {
                    $stats['exhibitions_skipped']++;
                }

                if ($existing->import_batch_id !== $batch->id) {
                    $existing->forceFill(['import_batch_id' => $batch->id])->save();
                }

                $exhibitionMap[(int) $legacyExhibition->id] = $existing->id;
            }

            $legacyContacts = DB::connection($legacyConnection)
                ->table('contacts')
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            foreach ($legacyContacts as $legacyContact) {
                if (! isset($exhibitionMap[(int) $legacyContact->exhibition_id])) {
                    $warnings[] = "Skipped legacy contact #{$legacyContact->id}: exhibition_id {$legacyContact->exhibition_id} is not importable for legacy tenant {$legacyTenantId}.";
                    $stats['warnings']++;

                    continue;
                }

                $stats['legacy_contacts_seen']++;

                $identity = $this->batchService->legacyIdentity($targetTenant->id, $legacySource, (int) $legacyContact->id);
                $existing = Contact::query()->where($identity)->first();

                $normalizedPath = $this->normalizeLegacyBusinessCardPath(
                    $legacyContact->business_card_path,
                    $legacyStorageBasePath,
                );

                $businessPayload = [
                    'tenant_id' => $targetTenant->id,
                    'exhibition_id' => $exhibitionMap[(int) $legacyContact->exhibition_id],
                    'first_name' => (string) $legacyContact->first_name,
                    'last_name' => (string) $legacyContact->last_name,
                    'email' => $legacyContact->email,
                    'phone' => $legacyContact->phone,
                    'company' => $legacyContact->company,
                    'note' => $legacyContact->note,
                    'file_path' => $normalizedPath,
                    'source' => $legacyContact->status === 'public' ? 'public' : 'internal',
                    'legacy_source' => $legacySource,
                    'legacy_id' => (int) $legacyContact->id,
                    'legacy_updated_at' => $legacyContact->updated_at,
                ];

                if ($existing === null) {
                    Contact::query()->create([
                        ...$businessPayload,
                        'import_batch_id' => $batch->id,
                    ]);
                    $stats['contacts_created']++;

                    continue;
                }

                if ($this->hasDirtyBusinessChanges($existing, $businessPayload)) {
                    $existing->fill($businessPayload);
                    $existing->save();
                    $stats['contacts_updated']++;
                } else {
                    $stats['contacts_skipped']++;
                }

                if ($existing->import_batch_id !== $batch->id) {
                    $existing->forceFill(['import_batch_id' => $batch->id])->save();
                }
            }

            $legacyTokens = DB::connection($legacyConnection)
                ->table('contact_reg_tokens')
                ->orderBy('exhibition_id')
                ->get();

            foreach ($legacyTokens as $legacyTokenRow) {
                $newExhibitionId = $exhibitionMap[(int) $legacyTokenRow->exhibition_id] ?? null;

                if ($newExhibitionId === null) {
                    continue;
                }

                $newExhibition = Exhibition::query()->find($newExhibitionId);

                if ($newExhibition === null) {
                    continue;
                }

                $token = (string) $legacyTokenRow->token;
                $tokenOwner = Exhibition::query()
                    ->where('public_token', $token)
                    ->where('id', '!=', $newExhibition->id)
                    ->first();

                if ($tokenOwner !== null) {
                    throw new RuntimeException(
                        "Public token collision for legacy exhibition {$legacyTokenRow->exhibition_id}: token [{$token}] is already assigned to exhibition {$tokenOwner->id}."
                    );
                }

                if ($newExhibition->public_token !== $token) {
                    $newExhibition->forceFill([
                        'public_token' => $token,
                        'import_batch_id' => $batch->id,
                    ])->save();

                    $stats['tokens_updated']++;
                }
            }

            $summary = array_merge($stats, [
                'target_tenant_id' => $targetTenant->id,
                'legacy_tenant_id' => $legacyTenantId,
                'legacy_connection' => $legacyConnection,
                'soft_deleted_policy' => 'excluded (deleted_at IS NULL only)',
                'warnings_list' => $warnings,
            ]);

            $this->batchService->completeBatch($batch, $summary);

            return ['batch_id' => $batch->id, ...$summary];
        } catch (\Throwable $e) {
            $this->batchService->failBatch($batch, $e->getMessage(), [
                ...$stats,
                'target_tenant_id' => $targetTenant->id,
                'legacy_tenant_id' => $legacyTenantId,
                'legacy_connection' => $legacyConnection,
                'warnings_list' => $warnings,
            ]);

            throw $e;
        }
    }

    private function hasDirtyBusinessChanges(Exhibition|Contact $model, array $businessPayload): bool
    {
        $copy = $model->replicate();
        $copy->exists = true;
        $copy->fill($businessPayload);

        return $copy->isDirty();
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    private function normalizeLegacyBusinessCardPath(mixed $legacyPath, ?string $legacyStorageBasePath): ?string
    {
        if ($legacyPath === null || trim((string) $legacyPath) === '') {
            return null;
        }

        $path = ltrim((string) $legacyPath, '/');

        if ($legacyStorageBasePath === null || trim($legacyStorageBasePath) === '') {
            return $path;
        }

        return rtrim($legacyStorageBasePath, '/').'/'.$path;
    }
}
