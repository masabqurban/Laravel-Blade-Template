<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/**
 * Who may manage users. Laravel discovers this policy automatically, and
 * controllers, form requests and the frontend (via shared `can` flags) all
 * ask it, so the rules live in exactly one place.
 */
class UserPolicy
{
    /** Every signed-in user may browse the directory. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /** Admins may delete anyone except themselves, so nobody locks everyone out. */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model);
    }

    /** Changing a role or status is an admin action, and never on yourself. */
    public function changeAccess(User $user, User $model): bool
    {
        return $user->role === Role::Admin && $user->isNot($model);
    }
}
