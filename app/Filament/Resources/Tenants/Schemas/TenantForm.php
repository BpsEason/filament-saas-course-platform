<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList; // 🚀 導入

class TenantForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make('基本資訊')
                ->schema([
                    TextInput::make('name')
                        ->label('租戶名稱')
                        ->required(),
                    TextInput::make('domain')
                        ->label('網域')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Toggle::make('is_active')
                        ->label('啟用狀態')
                        ->default(true),
                ]),

            // 🚀 核心修正：新增功能模組授權控制
            Section::make('功能授權')
                ->description('由 Super Admin 決定此租戶可使用的模組')
                ->schema([
                    CheckboxList::make('plan_features') // 確保 Tenant Model 的 $fillable 有此欄位
                        ->label('授權模組')
                        ->options([
                            'courses' => '課程管理系統',
                            'enrollments' => '報名管理系統',
                            'analytics' => '進階數據分析',
                        ])
                        ->columns(3)
                        ->gridDirection('row'),
                ]),
        ]);
    }
}