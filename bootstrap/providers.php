<?php

return [
    App\Providers\AppServiceProvider::class,

    // 🚀 註冊管理員面板 (負責管理 Tenant)
    App\Providers\Filament\AdminPanelProvider::class,

    // 🚀 註冊租戶業務面板 (負責管理 Course)
    // 務必確保 app/Providers/Filament/AppPanelProvider.php 檔案存在
    App\Providers\Filament\AppPanelProvider::class,
];
