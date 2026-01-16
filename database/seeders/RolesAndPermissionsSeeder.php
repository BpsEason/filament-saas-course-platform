<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 🚀 1. 重置快取，確保權限變動立即生效
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 🚀 2. 關鍵：建立權限時必須將 Team ID 設為 null (全域權限庫)
        setPermissionsTeamId(null);

        $permissions = [
            // --- 使用者管理 (User) ---
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'force_delete_user',
            'force_delete_any_user',
            'restore_user',
            'restore_any_user',
            'replicate_user',
            'reorder_user',

            // --- 角色管理 (Role) ---
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
            'force_delete_role',
            'force_delete_any_role',
            'restore_role',
            'restore_any_role',
            'reorder_role',

            // --- 租戶管理 (Tenant) ---
            'view_any_tenant',
            'view_tenant',
            'create_tenant',
            'update_tenant',
            'delete_tenant',

            // --- 課程管理 (Course) ---
            'view_any_course',
            'view_course',
            'create_course',
            'update_course',
            'delete_course',
            'delete_any_course',
            'publish_course',
            'reorder_course',

            // --- 報名管理 (Enrollment) ---
            'view_any_enrollment',
            'view_enrollment',
            'create_enrollment',
            'update_enrollment',
            'delete_enrollment',
            'enroll_course',

            // --- 系統功能 ---
            'shield_role',
            'view_analytics',
            'export_reports',
            'view_revenue',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $this->command->info('✅ 全域權限庫 (Total: ' . count($permissions) . ') 初始化成功。');
    }
}