<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends SpatieRole
{
    use HasFactory;

    /**
     * 🚀 讓 Filament 與 Spatie 權限系統對接
     * * 在 Spatie 的多租戶模式下，使用的是 'team_id'。
     * 這裡建立一個名為 'tenant' 的關聯，讓 Filament 的 Table Column 
     * 可以直接透過 TextColumn::make('tenant.name') 抓到租戶名稱。
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }

    /**
     * 💡 架構師提示：
     * 如果你未來想要針對特定租戶做 Scope 查詢，可以在這裡加上：
     * protected static function booted()
     * {
     * static::creating(fn ($role) => $role->team_id ??= \Filament\Facades\Filament::getTenant()?->id);
     * }
     */
}