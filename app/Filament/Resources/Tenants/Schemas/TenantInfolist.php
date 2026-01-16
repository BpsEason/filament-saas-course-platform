<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Infolists\Infolist; // 🚀 修正：改用 Infolist
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class TenantInfolist
{
    /**
     * @param Infolist $infolist // 🚀 修正型別提示
     * @return Infolist
     */
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('租戶詳情')
                ->schema([
                    TextEntry::make('name')->label('名稱'),
                    TextEntry::make('domain')->label('網域'),
                ]),
        ]);
    }
}
