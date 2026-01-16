<?php

namespace App\Filament\App\Resources\CourseResource\Schemas;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload; // 🚀 引入上傳組件
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make('課程包裝')->schema([
                // 🚀 加入封面圖上傳
                FileUpload::make('thumbnail')
                    ->label('課程封面')
                    ->image()
                    ->imageEditor() // 允許簡單裁剪，保持 Vlog 質感的比例
                    ->directory('course-thumbnails')
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('課程名稱')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                TextInput::make('slug')
                    ->label('短網址 (Slug)')
                    ->required()
                    ->unique(ignoreRecord: true),
            ])->columns(2),

            Section::make('銷售與狀態')->schema([
                TextInput::make('price')
                    ->label('課程售價')
                    ->numeric()
                    ->prefix('TWD')
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('立即發佈')
                    ->default(false),
            ])->columns(2),

            Section::make('詳細內容')->schema([
                RichEditor::make('description')
                    ->label('課程介紹')
                    ->columnSpanFull(),
            ]),
        ]);
    }
}