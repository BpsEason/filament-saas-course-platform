<?php

namespace App\Filament\App\Resources\EnrollmentResource\Pages; // 🚀 修正 Namespace

use App\Filament\App\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * 編輯完跳轉回列表頁
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
