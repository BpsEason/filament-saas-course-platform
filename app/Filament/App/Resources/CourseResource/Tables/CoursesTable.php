<?php

namespace App\Filament\App\Resources\CourseResource\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn; // 🚀 引入圖片欄位
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ActionGroup;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            // 🚀 加入圓形封面預覽，增加視覺張力
            ImageColumn::make('thumbnail')
                ->label('封面')
                ->circular()
                ->disk('public'),

            TextColumn::make('title')
                ->label('課程名稱')
                ->searchable()
                ->sortable(),

            TextColumn::make('price')
                ->label('價格')
                ->money('TWD')
                ->sortable(),

            IconColumn::make('is_active')
                ->label('狀態')
                ->boolean()
                ->sortable(),
        ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                ]),
            ]);
    }
}