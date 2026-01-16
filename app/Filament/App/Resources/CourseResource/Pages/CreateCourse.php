<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Filament\App\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 🚀 確保自動注入當前租戶 ID 與當前登入者作為老師 ID
        $data['tenant_id'] = Filament::getTenant()->id;
        $data['user_id'] = auth()->id();

        return $data;
    }

    /**
     * 創建後跳轉到列表頁
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
