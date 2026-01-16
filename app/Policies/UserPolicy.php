<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @return void|bool
     */
    public function before(User $user)
    {
        // For super admins, grant all permissions.
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_user');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('view_user');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_user');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('update_user');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('delete_user');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('force_delete_user');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_user');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('restore_user');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_user');
    }

    public function replicate(User $user, User $model): bool
    {
        return $user->hasPermissionTo('replicate_user');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_user');
    }
}