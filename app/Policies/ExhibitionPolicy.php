<?php

namespace App\Policies;

use App\Models\Exhibition;
use App\Models\User;
use App\Support\TenantContext;

class ExhibitionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Exhibition $exhibition): bool
    {
        return $this->isInCurrentTenant($exhibition);
    }

    public function create(User $user): bool
    {
        return app(TenantContext::class)->currentFromRequest(request()) !== null;
    }

    public function update(User $user, Exhibition $exhibition): bool
    {
        return $this->isInCurrentTenant($exhibition);
    }

    public function delete(User $user, Exhibition $exhibition): bool
    {
        return $this->isInCurrentTenant($exhibition);
    }

    private function isInCurrentTenant(Exhibition $exhibition): bool
    {
        $currentTenant = app(TenantContext::class)->currentFromRequest(request());

        return $currentTenant !== null && $exhibition->tenant_id === $currentTenant->id;
    }
}
