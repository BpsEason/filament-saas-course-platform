<?php

return [
    'shield_resource' => [
        // 🚀 關鍵修正 1：改為 false
        // 既然我們已經手寫了 RoleResource.php 來做在地化與租戶隔離，
        // 就不需要 Shield 插件再自動註冊一個「角色」選單，這樣重複的問題就解決了！
        'should_register_navigation' => false,

        'slug' => 'shield/roles',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => true,
        'is_globally_searchable' => false,
        'show_model_path' => true,

        // 🚀 關鍵修正 2：保持 false
        // Admin Panel 必須能跨租戶管理所有角色
        'is_scoped_to_tenant' => false,

        'cluster' => null,
    ],

    'tenant_model' => 'App\\Models\\Tenant',

    'auth_provider_model' => [
        'fqcn' => 'App\\Models\\User',
    ],

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,

        // 🚀 關鍵修正 3：建議改為 'after'
        // 設為 after 代表 Laravel 會先跑 Policy 判定。
        // 這很重要，因為如果你要在 Policy 裡寫「某些資源即使是 Admin 也不能刪除」的邏輯，
        // 'after' 才能讓你的自定義邏輯生效；'before' 會讓 Admin 暴力通行。
        'intercept_gate' => 'after',
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ],
        'page' => 'page',
        'widget' => 'widget',
    ],

    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        // 🚀 關鍵修正 4：開啟自定義權限
        // 這樣你在 Seeder 裡寫的 'publish_course' 等自定義權限才能被 Shield 介面識別
        'custom_permissions' => true,
    ],

    'generator' => [
        'option' => 'policies_and_permissions', // 生成權限的同時也自動生成 Policy 檔案
        'policy_directory' => 'Policies',
        'policy_namespace' => 'Policies',
    ],

    'exclude' => [
        'enabled' => true, // 確保這裡是 true
        'pages' => ['Dashboard'],
        'widgets' => ['AccountWidget', 'FilamentInfoWidget'],

        // 🚀 關鍵修正：排除掉 Tenant 資源
        'resources' => [
            'TenantResource',
        ],

        // 🚀 同時排除模型（這會影響 Shield 的自動生成邏輯）
        'models' => [
            'App\Models\Tenant',
            'Tenant',
        ],
    ],

    'discovery' => [
        // 🚀 關鍵修正 5：在多租戶架構下建議全部設為 true
        // 確保 Shield 能自動發現 App/Resources 與 App/App/Resources 下的所有業務資源
        'discover_all_resources' => true,
        'discover_all_widgets' => true,
        'discover_all_pages' => true,
    ],

    'register_role_policy' => [
        'enabled' => true,
    ],
];