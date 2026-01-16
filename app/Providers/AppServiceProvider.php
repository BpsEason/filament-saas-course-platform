<?php

namespace App\Providers;

use App\Models\Course;
use App\Policies\CoursePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 1. 註冊 Policy (手動關聯 Model 與 Policy)
        // 雖然 Laravel 會嘗試自動偵測，但在大型 SaaS 架構中，
        // 手動註冊能提升效能並避免命名空間導致的誤判。
        Gate::policy(Course::class, CoursePolicy::class);

        // 🚀 2. 定義超級管理員「特權鑰匙」
        // 無論 Policy 怎麼寫，超級管理員 (System Admin) 都能無視規則。
        // 這在客服除錯或處理緊急訂閱問題時非常有用。
        Gate::before(function ($user, $ability) {
            return $user->email === 'admin@system.com' ? true : null;
        });

        // 🚀 3. (選配) 強制多租戶資料隔離的嚴謹性
        // 如果你未來有開發 API，這能確保所有關聯查詢都受到保護
        // \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());
    }
}
