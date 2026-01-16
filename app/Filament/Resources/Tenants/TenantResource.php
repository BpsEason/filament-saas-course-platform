<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages;
use App\Filament\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Resources\Tenants\Schemas\TenantInfolist;
use App\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use Filament\Forms\Form; // 🚀 修正：v3 使用 Form 而非 Schema
use Filament\Infolists\Infolist; // 🚀 修正：v3 使用 Infolist 而非 Schema
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    // --- 🌍 語系與導覽設定 (修正型別以對齊父類別) ---

    protected static ?string $navigationLabel = '租戶管理';

    protected static ?string $modelLabel = '租戶';

    protected static ?string $pluralModelLabel = '租戶管理';

    // 🚀 關鍵修正：必須精確使用 ?string
    protected static ?string $navigationGroup = '系統設定';

    // 🚀 關鍵修正：必須精確使用 ?string
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $slug = 'tenants';

    /**
     * 配置表單
     */
    public static function form(Form $form): Form
    {
        return TenantForm::configure($form);
    }

    /**
     * 配置詳情頁
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return TenantInfolist::configure($infolist);
    }

    /**
     * 配置列表表格
     */
    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table)
            ->filters([
                TrashedFilter::make()
                    ->label('回收站'),
            ])
            ->actions([
                ViewAction::make()->label('查看'),
                EditAction::make()->label('編輯'),
                ActionGroup::make([
                    DeleteAction::make()->label('軟刪除'),
                    RestoreAction::make()->label('還原'),
                    ForceDeleteAction::make()->label('永久刪除'),
                ])
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('批次刪除'),
                    RestoreBulkAction::make()->label('批次還原'),
                    ForceDeleteBulkAction::make()->label('批次永久刪除'),
                ])->label('批次操作'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view'   => Pages\ViewTenant::route('/{record}'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}