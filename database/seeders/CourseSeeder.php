<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 取得所有租戶
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('找不到任何租戶 (Tenant)，請先執行 Tenant 相關 Seeder。');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->command->info("正在為租戶：{$tenant->name} 生成課程...");

            // 🚀 關鍵 1：切換 Spatie Team ID 以便正確查詢該租戶下的老師角色
            setPermissionsTeamId($tenant->id);
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // 1. 尋找該租戶下的老師
            $teacher = $tenant->users()
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'teacher');
                })->first() ?? $tenant->users()->first();

            if (!$teacher) {
                $this->command->error("  ❌ 錯誤：租戶 {$tenant->name} 內找不到任何使用者，跳過課程建立。");
                continue;
            }

            $this->command->info("  👤 授課老師：{$teacher->name} ({$teacher->email})");

            // 2. 建立課程數據
            $courses = [
                [
                    'title' => "基礎程式設計", // 移除前綴，讓 Slug 更漂亮
                    'description' => '這是一門專門為初學者設計的入門課程，涵蓋基礎語法與邏輯開發。',
                    'price' => 1200,
                ],
                [
                    'title' => "進階架構實戰",
                    'description' => '深入探討大型分散式系統的設計細節，包含微服務與高併發處理。',
                    'price' => 3500,
                ],
                [
                    'title' => "雲端原生部署指南",
                    'description' => '學習如何使用 K8s 與 AWS 建立彈性可伸縮的雲端基礎設施。',
                    'price' => 2800,
                ],
            ];

            foreach ($courses as $courseData) {
                // 🚀 關鍵 2：根據 Migration 要求生成 slug
                $slug = Str::slug($courseData['title']);

                Course::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'slug'      => $slug, // 配合 Migration 的 unique(['tenant_id', 'slug'])
                    ],
                    [
                        'user_id'      => $teacher->id,
                        'title'        => "{$tenant->name} - " . $courseData['title'],
                        'description'  => $courseData['description'],
                        'price'        => $courseData['price'],
                        'is_active'    => true,  // 🚀 修正：欄位名稱應為 is_active
                        'published_at' => now(), // 🚀 修正：補上 Migration 裡的發布時間
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );
            }

            $this->command->info("  ✅ 已成功為 {$tenant->name} 建立 " . count($courses) . " 堂課程。");
        }

        // 🚀 掃尾：重置狀態
        setPermissionsTeamId(null);
    }
}