<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_initializes_current_tenant_in_session_for_single_tenant_user(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::query()->create(['name' => 'Tenant Solo']);
        $user->tenants()->attach($tenant->id, ['role' => 'owner']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas(TenantContext::SESSION_KEY, $tenant->id);
    }

    public function test_login_uses_deterministic_fallback_for_multi_tenant_user(): void
    {
        $user = User::factory()->create();

        $firstTenant = Tenant::query()->create(['name' => 'Tenant A']);
        $secondTenant = Tenant::query()->create(['name' => 'Tenant B']);

        $user->tenants()->attach($secondTenant->id, ['role' => 'member']);
        $user->tenants()->attach($firstTenant->id, ['role' => 'owner']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHas(TenantContext::SESSION_KEY, $firstTenant->id);
    }

    public function test_middleware_corrects_invalid_tenant_id_stored_in_session(): void
    {
        $user = User::factory()->create();
        $validTenant = Tenant::query()->create(['name' => 'Valid Tenant']);
        $invalidTenant = Tenant::query()->create(['name' => 'Invalid Tenant']);

        $user->tenants()->attach($validTenant->id, ['role' => 'owner']);

        $response = $this->actingAs($user)
            ->withSession([TenantContext::SESSION_KEY => $invalidTenant->id])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSessionHas(TenantContext::SESSION_KEY, $validTenant->id);
    }

    public function test_middleware_redirects_authenticated_users_without_any_tenant_to_explicit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('tenant.missing'));
    }

    public function test_existing_user_without_tenant_can_still_login_and_hits_explicit_missing_tenant_flow(): void
    {
        $user = User::factory()->create();

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $loginResponse->assertRedirect(route('dashboard', absolute: false));

        $dashboardResponse = $this->get(route('dashboard'));

        $dashboardResponse->assertRedirect(route('tenant.missing'));
    }

    public function test_missing_tenant_page_is_accessible_with_authentication_without_loop(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tenant.missing'));

        $response->assertForbidden();
    }

    public function test_middleware_returns_explicit_forbidden_on_json_requests_without_tenant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('exhibitions.index'));

        $response->assertForbidden();
    }
}
