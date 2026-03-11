<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

class TenantContext
{
    public const SESSION_KEY = 'current_tenant_id';

    public function resolveForUser(User $user, Store $session): ?Tenant
    {
        $tenantId = $session->get(self::SESSION_KEY);

        if ($tenantId !== null) {
            $tenant = $user->tenants()->whereKey($tenantId)->first();

            if ($tenant !== null) {
                return $tenant;
            }
        }

        $fallbackTenant = $this->fallbackTenantForUser($user);

        if ($fallbackTenant !== null) {
            $session->put(self::SESSION_KEY, $fallbackTenant->id);

            return $fallbackTenant;
        }

        $session->forget(self::SESSION_KEY);

        return null;
    }

    public function fallbackTenantForUser(User $user): ?Tenant
    {
        return $user->tenants()->orderBy('tenants.id')->first();
    }

    public function currentFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('currentTenant');

        return $tenant instanceof Tenant ? $tenant : null;
    }

    public function resolveForBinding(Request $request): ?Tenant
    {
        $tenantFromAttributes = $this->currentFromRequest($request);

        if ($tenantFromAttributes !== null) {
            return $tenantFromAttributes;
        }

        $user = $request->user();

        if (! $user instanceof User || ! $request->hasSession()) {
            return null;
        }

        return $this->resolveForUser($user, $request->session());
    }
}
