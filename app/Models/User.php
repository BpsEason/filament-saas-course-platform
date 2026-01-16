<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasTenants, FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* -------------------------------------------------------------------------- */
    /* 🚀 Filament 面板存取權限 (最終修正版)                                          */
    /* -------------------------------------------------------------------------- */

    public function canAccessPanel(Panel $panel): bool
    {
        // 1. 如果是超級管理員 (Email 結尾符合)，在任何地方都放行
        if (str_ends_with($this->email, '@system.com')) {
            return true;
        }

        // 2. 判斷面板類型
        if ($panel->getId() === 'admin') {
            // 進入總管理後台：檢查是否有 super_admin 角色 (不限租戶)
            return $this->roles()->withoutGlobalScopes()->where('name', 'super_admin')->exists();
        }

        if ($panel->getId() === 'app') {
            // 進入租戶業務面板：只要使用者有隸屬任何租戶就先放行
            // 進去後 Resource 的權限檢查會根據當前 Tenant 正確運作
            return $this->tenants()->exists();
        }

        return false;
    }

    /* -------------------------------------------------------------------------- */
    /* 🚀 多租戶實作 (HasTenants)                                                 */
    /* -------------------------------------------------------------------------- */

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)->withTimestamps();
    }

    public function getTenants(Panel $panel): Collection
    {
        // 超級管理員在任何面板都能看到所有租戶
        if (str_ends_with($this->email, '@system.com') || $this->id === 1) {
            return Tenant::all();
        }

        // 一般使用者（包含學校管理員）只能看到自己隸屬的租戶
        return $this->tenants;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        /** @var Tenant $tenant */

        // 1. 超級管理員擁有所有租戶的通行證
        if (str_ends_with($this->email, '@system.com') || $this->id === 1) {
            return true;
        }

        // 2. 檢查該使用者是否隸屬該租戶，且租戶必須啟用
        // 💡 使用 exists() 檢查比 contains 更節省記憶體（避免載入所有模型）
        return $this->tenants()->where('tenants.id', $tenant->id)->exists() && $tenant->is_active;
    }
}