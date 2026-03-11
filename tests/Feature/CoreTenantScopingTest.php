<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CoreTenantScopingTest extends TestCase
{
    use RefreshDatabase;


    public function test_exhibition_binding_is_tenant_scoped_even_without_custom_tenant_middleware(): void
    {
        [$user, $tenant] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $outsideExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Outside Tenant Exhibition',
            'date' => now()->toDateString(),
        ]);

        Route::middleware('auth')->get('/_test/binding/exhibitions/{exhibition}', function (Exhibition $exhibition) {
            return response('ok');
        })->name('tests.binding.exhibitions.show');

        $this->actingAs($user)
            ->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->get(route('tests.binding.exhibitions.show', $outsideExhibition))
            ->assertNotFound();
    }

    public function test_contact_binding_is_tenant_scoped_for_update_delete_download_and_preview_without_custom_tenant_middleware(): void
    {
        Storage::fake();

        [$user, $tenant] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $outsideExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Outside Tenant Exhibition',
            'date' => now()->toDateString(),
        ]);

        $filePath = UploadedFile::fake()->image('outside.png')->store('contact-files');

        $outsideContact = Contact::query()->create([
            'exhibition_id' => $outsideExhibition->id,
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Outside',
            'last_name' => 'Contact',
            'source' => 'internal',
            'file_path' => $filePath,
            'file_original_name' => 'outside.png',
            'file_mime' => 'image/png',
            'file_size' => 100,
        ]);

        Route::middleware('auth')->group(function () {
            Route::put('/_test/binding/exhibitions/{exhibition}/contacts/{contact}', function (Exhibition $exhibition, Contact $contact) {
                return response('ok');
            })->name('tests.binding.contacts.update');

            Route::delete('/_test/binding/exhibitions/{exhibition}/contacts/{contact}', function (Exhibition $exhibition, Contact $contact) {
                return response('ok');
            })->name('tests.binding.contacts.destroy');

            Route::get('/_test/binding/exhibitions/{exhibition}/contacts/{contact}/download', function (Exhibition $exhibition, Contact $contact) {
                return response('ok');
            })->name('tests.binding.contacts.download');

            Route::get('/_test/binding/exhibitions/{exhibition}/contacts/{contact}/preview', function (Exhibition $exhibition, Contact $contact) {
                return response('ok');
            })->name('tests.binding.contacts.preview');
        });

        $client = $this->actingAs($user)->withSession([TenantContext::SESSION_KEY => $tenant->id]);

        $client->put(route('tests.binding.contacts.update', [$outsideExhibition, $outsideContact]))->assertNotFound();
        $client->delete(route('tests.binding.contacts.destroy', [$outsideExhibition, $outsideContact]))->assertNotFound();
        $client->get(route('tests.binding.contacts.download', [$outsideExhibition, $outsideContact]))->assertNotFound();
        $client->get(route('tests.binding.contacts.preview', [$outsideExhibition, $outsideContact]))->assertNotFound();
    }

    public function test_public_routes_work_without_authentication_and_without_tenant_session_context(): void
    {
        $owner = User::factory()->create();
        $tenant = Tenant::query()->create(['name' => 'Public Tenant']);
        $owner->tenants()->attach($tenant->id, ['role' => 'owner']);

        $exhibition = Exhibition::query()->create([
            'user_id' => $owner->id,
            'tenant_id' => $tenant->id,
            'name' => 'Public Expo',
            'date' => now()->toDateString(),
            'public_token' => 'public-token-123',
        ]);

        $this->get(route('public.form', ['token' => $exhibition->public_token]))->assertOk();

        $this->post(route('public.store', ['token' => $exhibition->public_token]), [
            'first_name' => 'Public',
            'last_name' => 'Lead',
            'email' => 'public@example.com',
        ])->assertRedirect(route('public.thanks', ['token' => $exhibition->public_token]));

        $this->assertDatabaseHas('contacts', [
            'exhibition_id' => $exhibition->id,
            'tenant_id' => $tenant->id,
            'first_name' => 'Public',
            'last_name' => 'Lead',
            'source' => 'public',
        ]);
    }

    public function test_listing_are_filtered_by_current_tenant(): void
    {
        [$user, $tenant] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $visibleExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'name' => 'Expo Tenant A',
            'date' => now()->toDateString(),
        ]);

        Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Expo Tenant B',
            'date' => now()->toDateString(),
        ]);

        Contact::query()->create([
            'exhibition_id' => $visibleExhibition->id,
            'tenant_id' => $tenant->id,
            'first_name' => 'Visible',
            'last_name' => 'Contact',
            'source' => 'internal',
        ]);

        $hiddenExhibition = Exhibition::query()->where('tenant_id', $otherTenant->id)->firstOrFail();
        Contact::query()->create([
            'exhibition_id' => $hiddenExhibition->id,
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Hidden',
            'last_name' => 'Contact',
            'source' => 'internal',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Expo Tenant A');
        $response->assertDontSee('Expo Tenant B');
        $response->assertSee('1');

        $indexResponse = $this->actingAs($user)->get(route('exhibitions.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Expo Tenant A');
        $indexResponse->assertDontSee('Expo Tenant B');
    }

    public function test_exhibition_outside_tenant_is_not_accessible_for_show_and_export(): void
    {
        [$user] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $otherExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant Expo',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('exhibitions.show', $otherExhibition))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('exhibitions.contacts.export', $otherExhibition))
            ->assertNotFound();
    }

    public function test_contact_outside_tenant_and_related_file_are_not_accessible(): void
    {
        Storage::fake();

        [$user, $tenant] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $myExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'name' => 'My Expo',
            'date' => now()->toDateString(),
        ]);

        $otherExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Expo',
            'date' => now()->toDateString(),
        ]);

        $filePath = UploadedFile::fake()->image('outside.png')->store('contact-files');

        $outsideContact = Contact::query()->create([
            'exhibition_id' => $otherExhibition->id,
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Outside',
            'last_name' => 'Tenant',
            'source' => 'internal',
            'file_path' => $filePath,
            'file_original_name' => 'outside.png',
            'file_mime' => 'image/png',
            'file_size' => 100,
        ]);

        $this->actingAs($user)
            ->put(route('exhibitions.contacts.update', [$myExhibition, $outsideContact]), [
                'first_name' => 'Tampered',
                'last_name' => 'Contact',
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('exhibitions.contacts.preview', [$myExhibition, $outsideContact]))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('exhibitions.contacts.download', [$myExhibition, $outsideContact]))
            ->assertNotFound();
    }

    public function test_create_store_on_other_tenant_exhibition_is_blocked(): void
    {
        [$user] = $this->makeUserWithTenant('Tenant A');
        $otherTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $otherExhibition = Exhibition::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Expo',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('exhibitions.contacts.store', $otherExhibition), [
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'email' => 'mario@example.com',
            ])
            ->assertNotFound();
    }


    public function test_new_exhibitions_are_associated_to_current_tenant(): void
    {
        [$user, $tenant] = $this->makeUserWithTenant('Tenant A');

        $this->actingAs($user)
            ->post(route('exhibitions.store'), [
                'name' => 'Scoped Expo',
                'date_mode' => 'single',
                'date' => now()->toDateString(),
            ])
            ->assertRedirect(route('exhibitions.index'));

        $this->assertDatabaseHas('exhibitions', [
            'name' => 'Scoped Expo',
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);
    }

    private function makeUserWithTenant(string $tenantName): array
    {
        $user = User::factory()->create();
        $tenant = Tenant::query()->create(['name' => $tenantName]);
        $user->tenants()->attach($tenant->id, ['role' => 'owner']);

        return [$user, $tenant];
    }
}
