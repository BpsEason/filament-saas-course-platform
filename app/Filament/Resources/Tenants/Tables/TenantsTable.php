<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. 租戶名稱
                TextColumn::make('name')
                    ->label('租戶名稱')
                    ->searchable()
                    ->sortable(),

                // 2. 網域
                TextColumn::make('domain')
                    ->label('網域')
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->searchable(),

                // 3. 訂閱等級
                TextColumn::make('subscription_level')
                    ->label('訂閱等級')
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'enterprise' => 'success',
                        'pro' => 'warning',
                        default => 'gray',
                    }),

                // 4. 啟用狀態
                IconColumn::make('is_active')
                    ->label('狀態')
                    ->boolean(),

                // 5. 建立時間
                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            // 🚀 修正點 1：將 recordActions 改為 actions
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            // 🚀 修正點 2：確保 BulkActionGroup 來自 Tables\Actions
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
