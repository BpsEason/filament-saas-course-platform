<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Filament\Models\Contracts\HasName;

class Tenant extends BaseTenant implements HasName
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'subscription_level',
        'is_active',
        'stripe_id',
        'plan_features', // 🚀 新增：存儲 Admin 勾選的功能開關
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_level' => 'string',
        'plan_features' => 'array', // 🚀 自動轉換為陣列
    ];

    /**
     * 🚀 實作 HasName 接口
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * 🚀 核心邏輯：自動生成 Slug
     */
    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            $tenant->domain = Str::lower(trim($tenant->domain));

            if (! $tenant->slug) {
                $tenant->slug = Str::slug($tenant->name);
            }

            // 初始化預設功能 (選配)
            if (empty($tenant->plan_features)) {
                $tenant->plan_features = ['courses'];
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /* 關係連結 (Relations)                                                       */
    /* -------------------------------------------------------------------------- */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    // 建議：將 Student 改為與 Tenant 關聯
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /* -------------------------------------------------------------------------- */
    /* 業務邏輯：功能授權判斷                                                       */
    /* -------------------------------------------------------------------------- */

    /**
     * 🚀 核心：判斷該租戶是否擁有某個功能模組
     * 由 Admin 在 TenantResource 勾選後控制
     */
    public function hasModule(string $module): bool
    {
        // 1. 如果租戶被停用，直接關閉所有模組
        if (! $this->is_active) return false;

        // 2. 為了開發方便，你可以定義一個「全功能租戶」的條件 (選配)
        // if ($this->subscription_level === 'enterprise') return true;

        // 3. 確保判斷邏輯正確
        return is_array($this->plan_features) && in_array(trim($module), $this->plan_features);
    }

    public function isPaidPlan(): bool
    {
        return in_array($this->subscription_level, ['pro', 'enterprise']);
    }
}