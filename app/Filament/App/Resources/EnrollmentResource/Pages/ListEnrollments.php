<?php

namespace App\Filament\App\Resources\EnrollmentResource\Pages; // 🚀 修正 Namespace

use App\Filament\App\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('手動報名學生')
                ->icon('heroicon-m-plus'),
        ];
    }
}
