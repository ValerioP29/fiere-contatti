<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\ImportBatch;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyCustomerImporterTest extends TestCase
{
    use RefreshDatabase;

    private string $legacyDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legacyDatabasePath = database_path('testing-legacy.sqlite');
        @unlink($this->legacyDatabasePath);
        touch($this->legacyDatabasePath);

        config()->set('database.connections.legacy', [
            'driver' => 'sqlite',
            'database' => $this->legacyDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('legacy');

        $this->createLegacySchema();
    }

    protected function tearDown(): void
    {
        DB::disconnect('legacy');
        @unlink($this->legacyDatabasePath);

        parent::tearDown();
    }

    public function test_it_imports_exhibitions_contacts_and_public_tokens_into_explicit_target_tenant(): void
    {
        $targetTenant = Tenant::query()->create(['name' => 'Storico']);
        $otherTenant = Tenant::query()->create(['name' => 'Altro']);

        $this->seedLegacyRows();

        $exitCode = Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
            '--legacy-storage-base-path' => '/legacy-storage',
        ]);

        $this->assertSame(0, $exitCode);

        $expoA = Exhibition::query()->where('legacy_id', 101)->where('tenant_id', $targetTenant->id)->firstOrFail();
        $expoB = Exhibition::query()->where('legacy_id', 102)->where('tenant_id', $targetTenant->id)->firstOrFail();

        $this->assertSame('TK-EXPO-A', $expoA->public_token);
        $this->assertSame('TK-EXPO-B', $expoB->public_token);

        $contact = Contact::query()->where('legacy_id', 501)->where('tenant_id', $targetTenant->id)->firstOrFail();

        $this->assertSame($expoA->id, $contact->exhibition_id);
        $this->assertSame('/legacy-storage/business_card_images/cards/a.png', $contact->file_path);

        $this->assertDatabaseMissing('exhibitions', [
            'tenant_id' => $otherTenant->id,
            'legacy_source' => 'legacy_customer_v1',
        ]);

        $this->assertDatabaseMissing('contacts', [
            'tenant_id' => $otherTenant->id,
            'legacy_source' => 'legacy_customer_v1',
        ]);
    }

    public function test_it_is_idempotent_and_updates_existing_records_without_duplicates(): void
    {
        $targetTenant = Tenant::query()->create(['name' => 'Storico']);

        $this->seedLegacyRows();

        Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        DB::connection('legacy')->table('exhibitions')->where('id', 101)->update([
            'name' => 'Expo A Updated',
            'updated_at' => '2026-01-01 10:00:00',
        ]);

        DB::connection('legacy')->table('contacts')->where('id', 501)->update([
            'email' => 'newmail@example.test',
            'updated_at' => '2026-01-01 10:00:00',
        ]);

        $secondRun = Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $this->assertSame(0, $secondRun);

        $this->assertSame(2, Exhibition::query()->where('tenant_id', $targetTenant->id)->where('legacy_source', 'legacy_customer_v1')->count());
        $this->assertSame(2, Contact::query()->where('tenant_id', $targetTenant->id)->where('legacy_source', 'legacy_customer_v1')->count());

        $this->assertDatabaseHas('exhibitions', [
            'tenant_id' => $targetTenant->id,
            'legacy_source' => 'legacy_customer_v1',
            'legacy_id' => 101,
            'name' => 'Expo A Updated',
        ]);

        $this->assertDatabaseHas('contacts', [
            'tenant_id' => $targetTenant->id,
            'legacy_source' => 'legacy_customer_v1',
            'legacy_id' => 501,
            'email' => 'newmail@example.test',
        ]);
    }

    public function test_it_marks_second_unchanged_run_as_skipped_not_updated(): void
    {
        $targetTenant = Tenant::query()->create(['name' => 'Storico']);

        $this->seedLegacyRows();

        Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $firstExhibitionUpdatedAt = Exhibition::query()->where('tenant_id', $targetTenant->id)->where('legacy_id', 101)->value('updated_at');
        $firstContactUpdatedAt = Contact::query()->where('tenant_id', $targetTenant->id)->where('legacy_id', 501)->value('updated_at');

        $secondRun = Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $this->assertSame(0, $secondRun);

        /** @var ImportBatch $batch */
        $batch = ImportBatch::query()->latest('id')->firstOrFail();
        $this->assertSame(0, $batch->summary['exhibitions_created']);
        $this->assertSame(0, $batch->summary['exhibitions_updated']);
        $this->assertGreaterThan(0, $batch->summary['exhibitions_skipped']);
        $this->assertSame(0, $batch->summary['contacts_created']);
        $this->assertSame(0, $batch->summary['contacts_updated']);
        $this->assertGreaterThan(0, $batch->summary['contacts_skipped']);

        $this->assertSame(
            $firstExhibitionUpdatedAt,
            Exhibition::query()->where('tenant_id', $targetTenant->id)->where('legacy_id', 101)->value('updated_at')
        );
        $this->assertSame(
            $firstContactUpdatedAt,
            Contact::query()->where('tenant_id', $targetTenant->id)->where('legacy_id', 501)->value('updated_at')
        );
    }

    public function test_it_fails_clearly_when_public_token_collides_with_another_exhibition(): void
    {
        $targetTenant = Tenant::query()->create(['name' => 'Storico']);

        $this->seedLegacyRows();

        Exhibition::query()->create([
            'tenant_id' => $targetTenant->id,
            'name' => 'Existing token owner',
            'date' => '2026-01-01',
            'public_token' => 'TK-EXPO-A',
        ]);

        $exitCode = Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Public token collision', Artisan::output());

        $failedBatch = ImportBatch::query()->latest('id')->firstOrFail();
        $this->assertSame('failed', $failedBatch->status);
        $this->assertStringContainsString('Public token collision', $failedBatch->summary['reason']);
    }

    public function test_it_respects_soft_deleted_policy_by_importing_only_deleted_at_null_records(): void
    {
        $targetTenant = Tenant::query()->create(['name' => 'Storico']);

        $this->seedLegacyRows();

        $exitCode = Artisan::call('legacy:import:customer', [
            '--tenant' => $targetTenant->id,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseMissing('exhibitions', [
            'tenant_id' => $targetTenant->id,
            'legacy_source' => 'legacy_customer_v1',
            'legacy_id' => 103,
        ]);

        $this->assertDatabaseMissing('contacts', [
            'tenant_id' => $targetTenant->id,
            'legacy_source' => 'legacy_customer_v1',
            'legacy_id' => 503,
        ]);

        /** @var ImportBatch $batch */
        $batch = ImportBatch::query()->latest('id')->firstOrFail();
        $this->assertSame('excluded (deleted_at IS NULL only)', $batch->summary['soft_deleted_policy']);
    }

    public function test_it_fails_clearly_when_target_tenant_does_not_exist(): void
    {
        $this->seedLegacyRows();

        $exitCode = Artisan::call('legacy:import:customer', [
            '--tenant' => 99999,
            '--source' => 'legacy_customer_v1',
            '--legacy-connection' => 'legacy',
            '--legacy-tenant-id' => 10,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Target tenant [99999] not found', Artisan::output());
    }

    private function createLegacySchema(): void
    {
        DB::connection('legacy')->statement('CREATE TABLE exhibitions (
            id INTEGER PRIMARY KEY,
            tenant_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            date TEXT NULL,
            start_date TEXT NULL,
            end_date TEXT NULL,
            company TEXT NULL,
            note TEXT NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL,
            deleted_at TEXT NULL
        )');

        DB::connection('legacy')->statement('CREATE TABLE contacts (
            id INTEGER PRIMARY KEY,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            company TEXT NULL,
            phone TEXT NULL,
            email TEXT NULL,
            note TEXT NULL,
            business_card_path TEXT NULL,
            status TEXT NULL,
            exhibition_id INTEGER NOT NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL,
            deleted_at TEXT NULL
        )');

        DB::connection('legacy')->statement('CREATE TABLE contact_reg_tokens (
            token TEXT NOT NULL,
            exhibition_id INTEGER NOT NULL UNIQUE
        )');
    }

    private function seedLegacyRows(): void
    {
        DB::connection('legacy')->table('exhibitions')->insert([
            [
                'id' => 101,
                'tenant_id' => 10,
                'name' => 'Expo A',
                'date' => '2025-05-20',
                'start_date' => null,
                'end_date' => null,
                'company' => 'Company A',
                'note' => 'Note A',
                'created_at' => '2025-01-01 10:00:00',
                'updated_at' => '2025-01-02 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 102,
                'tenant_id' => 10,
                'name' => 'Expo B',
                'date' => null,
                'start_date' => '2025-06-01',
                'end_date' => '2025-06-03',
                'company' => 'Company B',
                'note' => null,
                'created_at' => '2025-01-03 10:00:00',
                'updated_at' => '2025-01-04 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 103,
                'tenant_id' => 10,
                'name' => 'Expo Deleted',
                'date' => '2025-07-01',
                'start_date' => null,
                'end_date' => null,
                'company' => null,
                'note' => null,
                'created_at' => '2025-01-05 10:00:00',
                'updated_at' => '2025-01-06 10:00:00',
                'deleted_at' => '2025-03-01 00:00:00',
            ],
            [
                'id' => 201,
                'tenant_id' => 99,
                'name' => 'Other Tenant Expo',
                'date' => '2025-08-01',
                'start_date' => null,
                'end_date' => null,
                'company' => 'Other',
                'note' => null,
                'created_at' => '2025-01-07 10:00:00',
                'updated_at' => '2025-01-08 10:00:00',
                'deleted_at' => null,
            ],
        ]);

        DB::connection('legacy')->table('contacts')->insert([
            [
                'id' => 501,
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'company' => 'A Corp',
                'phone' => '123',
                'email' => 'mario@example.test',
                'note' => 'Lead',
                'business_card_path' => 'business_card_images/cards/a.png',
                'status' => 'public',
                'exhibition_id' => 101,
                'created_at' => '2025-02-01 10:00:00',
                'updated_at' => '2025-02-02 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 502,
                'first_name' => 'Giulia',
                'last_name' => 'Verdi',
                'company' => 'B Corp',
                'phone' => '456',
                'email' => 'giulia@example.test',
                'note' => null,
                'business_card_path' => null,
                'status' => 'internal',
                'exhibition_id' => 102,
                'created_at' => '2025-02-03 10:00:00',
                'updated_at' => '2025-02-04 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 503,
                'first_name' => 'Soft',
                'last_name' => 'Deleted',
                'company' => null,
                'phone' => null,
                'email' => null,
                'note' => null,
                'business_card_path' => 'business_card_images/cards/deleted.png',
                'status' => 'internal',
                'exhibition_id' => 101,
                'created_at' => '2025-02-05 10:00:00',
                'updated_at' => '2025-02-06 10:00:00',
                'deleted_at' => '2025-03-10 00:00:00',
            ],
            [
                'id' => 504,
                'first_name' => 'Cross',
                'last_name' => 'Tenant',
                'company' => null,
                'phone' => null,
                'email' => null,
                'note' => null,
                'business_card_path' => 'business_card_images/cards/cross.png',
                'status' => 'internal',
                'exhibition_id' => 201,
                'created_at' => '2025-02-05 10:00:00',
                'updated_at' => '2025-02-06 10:00:00',
                'deleted_at' => null,
            ],
        ]);

        DB::connection('legacy')->table('contact_reg_tokens')->insert([
            ['token' => 'TK-EXPO-A', 'exhibition_id' => 101],
            ['token' => 'TK-EXPO-B', 'exhibition_id' => 102],
            ['token' => 'TK-OTHER', 'exhibition_id' => 201],
        ]);
    }
}
