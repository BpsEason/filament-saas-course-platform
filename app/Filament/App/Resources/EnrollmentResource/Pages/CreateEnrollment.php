<?php

namespace App\Filament\App\Resources\EnrollmentResource\Pages; // 🚀 修正 Namespace

use App\Filament\App\Resources\EnrollmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    /**
     * 🚀 關鍵：在資料寫入 DB 前，自動注入當前租戶 ID
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()->id;

        return $data;
    }

    /**
     * 建立後跳轉回列表頁
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
