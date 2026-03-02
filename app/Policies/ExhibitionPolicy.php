<?php

namespace App\Policies;

use App\Models\Exhibition;
use App\Models\User;

class ExhibitionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Exhibition $exhibition): bool
    {
        return $exhibition->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Exhibition $exhibition): bool
    {
        return $exhibition->user_id === $user->id;
    }

    public function delete(User $user, Exhibition $exhibition): bool
    {
        return $exhibition->user_id === $user->id;
    }
}
