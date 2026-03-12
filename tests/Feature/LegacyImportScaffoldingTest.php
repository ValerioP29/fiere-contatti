<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\ImportBatch;
use App\Models\Tenant;
use App\Services\LegacyImport\LegacyImportScaffoldingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LegacyImportScaffoldingTest extends TestCase
{
    use RefreshDatabase;

    public function test_scaffolding_command_creates_tracked_import_batch(): void
    {
        $exitCode = Artisan::call('legacy:import:scaffold', [
            '--source' => 'legacy_app_v1',
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $batch = ImportBatch::query()->firstOrFail();

        $this->assertSame('legacy_app_v1', $batch->source);
        $this->assertTrue($batch->dry_run);
        $this->assertSame('completed', $batch->status);
        $this->assertNotNull($batch->started_at);
        $this->assertNotNull($batch->finished_at);
    }

    public function test_service_can_mark_batch_as_failed_for_auditing(): void
    {
        $service = app(LegacyImportScaffoldingService::class);
        $batch = $service->startBatch('legacy_app_v1');

        $failedBatch = $service->failBatch($batch, 'legacy db connection timeout', [
            'processed_rows' => 120,
        ]);

        $this->assertSame('failed', $failedBatch->status);
        $this->assertNotNull($failedBatch->finished_at);
        $this->assertSame('legacy db connection timeout', $failedBatch->summary['reason']);
        $this->assertSame(120, $failedBatch->summary['processed_rows']);
    }

    public function test_legacy_identity_helper_is_stable_and_used_for_lookup(): void
    {
        $service = app(LegacyImportScaffoldingService::class);
        $tenant = Tenant::query()->create(['name' => 'Tenant Import']);

        $identity = $service->legacyIdentity($tenant->id, 'legacy_app_v1', '101');

        $this->assertSame([
            'tenant_id' => $tenant->id,
            'legacy_source' => 'legacy_app_v1',
            'legacy_id' => 101,
        ], $identity);

        $exhibition = Exhibition::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Expo',
            'date' => '2025-01-01',
            'legacy_source' => 'legacy_app_v1',
            'legacy_id' => 101,
        ]);

        $contact = Contact::query()->create([
            'tenant_id' => $tenant->id,
            'exhibition_id' => $exhibition->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'source' => 'internal',
            'legacy_source' => 'legacy_app_v1',
            'legacy_id' => 501,
        ]);

        $foundExhibition = $service->findImportedExhibition($tenant->id, 'legacy_app_v1', 101);
        $foundContact = $service->findImportedContact($tenant->id, 'legacy_app_v1', '501');

        $this->assertNotNull($foundExhibition);
        $this->assertNotNull($foundContact);
        $this->assertSame($exhibition->id, $foundExhibition->id);
        $this->assertSame($contact->id, $foundContact->id);
    }
}
