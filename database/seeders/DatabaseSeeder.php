<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🚀 0. 初始化：徹底清除權限快取，防止舊的 Team ID 殘留
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->call(RolesAndPermissionsSeeder::class);
        $password = Hash::make('password');

        // ------------------------------------------------------------------
        // 🚀 1. 建立「全域」Super Admin (上帝視角)
        // ------------------------------------------------------------------
        setPermissionsTeamId(null); // 設定為全域空間
        $superAdminRole = Role::findOrCreate('super_admin', 'web');
        $superAdminRole->syncPermissions(Permission::all());

        $superAdminUser = User::updateOrCreate(
            ['email' => 'admin@system.com'],
            ['name' => 'System Super Admin', 'password' => $password, 'email_verified_at' => now()]
        );

        // 強制指派全域角色
        $this->forceAssignRole($superAdminUser, $superAdminRole->id, null);

        // ------------------------------------------------------------------
        // 🚀 2. 初始化租戶資料與校區使用者
        // ------------------------------------------------------------------
        $tenantsData = [
            'ntu' => [
                'name' => 'National Taiwan University',
                'admin_email' => 'admin@ntu.edu.tw',
                'teacher_email' => 'teacher@ntu.edu.tw',
                'student_email' => 'student@ntu.edu.tw',
            ],
            'google' => [
                'name' => 'Google Academy',
                'admin_email' => 'admin@google.com',
                'teacher_email' => 'teacher@google.com',
                'student_email' => 'student@google.com',
            ],
        ];

        foreach ($tenantsData as $slug => $data) {
            $tenant = Tenant::updateOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'domain' => "{$slug}.test",
                'is_active' => true,
                'plan_features' => ['courses', 'enrollments', 'analytics']
            ]);

            // 🚀 為該校初始化標準角色
            $this->initStandardRoles($tenant->id);

            // 🌟 核心修正：指派校區帳號角色 (確保 team_id 寫入)
            $this->createTenantUser($data['admin_email'], "{$slug} Admin", 'admin', $tenant->id, $password);
            $this->createTenantUser($data['teacher_email'], "{$slug} Teacher", 'teacher', $tenant->id, $password);
            $this->createTenantUser($data['student_email'], "{$slug} Student", 'student', $tenant->id, $password);

            // 讓總管理員也具備進入該校區的門票
            $superAdminUser->tenants()->syncWithoutDetaching([$tenant->id]);
        }

        // 🚀 3. 產生業務數據
        $this->call([
            CourseSeeder::class,
            EnrollmentSeeder::class,
        ]);

        setPermissionsTeamId(null); // 最後將全域狀態歸位
        $this->command->info('✅ SaaS 測試環境建置成功，帳號權限已精準指派！');
    }

    /**
     * 🚀 修正版：初始化租戶角色，並強制清除 Spatie 內部快取
     */
    protected function initStandardRoles(int $tenantId): void
    {
        setPermissionsTeamId($tenantId);
        // 強制叫 Spatie 重新整理 Team ID 的認知
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $adminPermissions = [
            'view_any_course',
            'view_course',
            'create_course',
            'update_course',
            'delete_course',
            'publish_course',
            'view_any_enrollment',
            'view_enrollment',
            'create_enrollment',
            'update_enrollment',
            'delete_enrollment',
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
        ];

        Role::findOrCreate('admin', 'web')->syncPermissions($adminPermissions);
        Role::findOrCreate('teacher', 'web')->syncPermissions([
            'view_any_course',
            'view_course',
            'create_course',
            'update_course',
            'publish_course',
            'view_any_enrollment',
            'view_enrollment',
            'view_any_user',
            'view_user',
        ]);
        Role::findOrCreate('student', 'web')->syncPermissions([
            'view_any_course',
            'view_course',
            'view_any_enrollment',
            'view_enrollment',
        ]);
    }

    protected function createTenantUser(string $email, string $name, string $roleName, int $tenantId, string $password): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'email_verified_at' => now()]
        );

        $user->tenants()->syncWithoutDetaching([$tenantId]);

        // 🚀 關鍵：直接根據名稱與 Team ID 找角色，避免 Spatie 抓錯
        $role = Role::where('name', $roleName)->where('team_id', $tenantId)->first();

        if ($role) {
            $this->forceAssignRole($user, $role->id, $tenantId);
        }
    }

    /**
     * 🚀 終極手段：直接操作樞紐表，確保 team_id 絕對正確
     */
    protected function forceAssignRole(User $user, int $roleId, ?int $tenantId): void
    {
        DB::table('model_has_roles')->updateOrInsert([
            'role_id'    => $roleId,
            'model_id'   => $user->id,
            'model_type' => User::class,
            'team_id'    => $tenantId, // 這裡是重點，沒寫這個值登入就會變無權限
        ]);
    }
}