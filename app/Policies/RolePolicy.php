<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Filament\Facades\Filament;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // Super Admin 擁有最高權限，跳過所有檢查
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    // 🚀 輔助方法：檢查該角色是否屬於當前租戶
    protected function isOwnedByCurrentTenant(Role $role): bool
    {
        $tenant = Filament::getTenant();

        // 如果目前不在租戶上下文（例如中央後台），且該角色是全域的 (team_id 為 null)
        if (!$tenant) {
            return $role->team_id === null;
        }

        // 檢查角色的 team_id 是否與當前登入的校區 ID 一致
        return (int) $role->team_id === (int) $tenant->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_role');
    }

    public function view(User $user, Role $role): bool
    {
        // 必須有權限 且 角色屬於該校區
        return $user->hasPermissionTo('view_role') && $this->isOwnedByCurrentTenant($role);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_role');
    }

    public function update(User $user, Role $role): bool
    {
        // 防止校區管理員修改到其他校區或是全域系統角色
        return $user->hasPermissionTo('update_role') && $this->isOwnedByCurrentTenant($role);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('delete_role') && $this->isOwnedByCurrentTenant($role);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_role');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('force_delete_role') && $this->isOwnedByCurrentTenant($role);
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('restore_role') && $this->isOwnedByCurrentTenant($role);
    }
}