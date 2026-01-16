<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

class RoleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = '角色管理';
    protected static ?string $modelLabel = '角色';
    protected static ?string $pluralModelLabel = '角色管理';
    protected static ?string $navigationGroup = '系統權限控制';

    /**
     * 🚀 修正：手動定義權限 UI 並加入中文化映射
     */
    public static function getShieldFormComponents(): array
    {
        // 1. 定義動作的中文化對照表
        $actionLabels = [
            'view'             => '檢視',
            'view_any'         => '列表',
            'create'           => '新增',
            'update'           => '編輯',
            'restore'          => '還原',
            'restore_any'      => '批量還原',
            'replicate'        => '複製',
            'reorder'          => '排序',
            'delete'           => '刪除',
            'delete_any'       => '批量刪除',
            'force_delete'     => '強制刪除',
            'force_delete_any' => '批量強制刪除',
            'publish'          => '發佈', // 針對 Course 等資源增加
        ];

        // 2. 取得所有資源並過濾
        $resources = collect(Filament::getResources())
            ->filter(function ($resource) {
                if (!auth()->user()?->hasRole('super_admin')) {
                    return !str_contains($resource, 'TenantResource');
                }
                return true;
            });

        $components = [];

        // 🚀 優化：靜態快取權限，避免在循環中重複查詢資料庫
        static $allPermissions = null;

        foreach ($resources as $resource) {
            $resourceClassName = Str::afterLast($resource, '\\');

            /**
             * 🚀 核心修正：對齊資料庫命名規範
             * 將 "UserResource" 轉換為 "user"，去掉 "Resource" 尾巴並轉為 snake_case
             * 這樣生成的權限名才會是 "view_user" 而不是 "view_user_resource"
             */
            $resourceKey = Str::snake(str_replace('Resource', '', $resourceClassName));

            $resourceLabel = method_exists($resource, 'getPluralModelLabel')
                ? $resource::getPluralModelLabel()
                : $resourceClassName;

            $permissionPrefixes = method_exists($resource, 'getPermissionPrefixes')
                ? $resource::getPermissionPrefixes()
                : ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];

            // 生成該資源區塊的所有選項
            $options = collect($permissionPrefixes)->mapWithKeys(function ($prefix) use ($resourceKey, $actionLabels) {
                $permissionName = $prefix . '_' . $resourceKey;
                $label = $actionLabels[$prefix] ?? $prefix;
                return [$permissionName => $label];
            });

            $components[] = Forms\Components\Section::make($resourceLabel)
                ->description("權限代碼關鍵字: " . $resourceKey)
                ->compact()
                ->schema([
                    Forms\Components\CheckboxList::make($resourceClassName) // 使用 Resource 類別名作為表單 Key
                        ->label('可執行的操作')
                        ->hiddenLabel()
                        ->options($options)
                        ->columns(4)
                        ->gridDirection('row')
                        ->bulkToggleable()
                        ->dehydrated(true)
                        ->formatStateUsing(function ($record) use ($options, &$allPermissions) {
                            if (!$record) return [];

                            // 🚀 效能優化：只查詢一次資料庫
                            if ($allPermissions === null) {
                                $allPermissions = $record->permissions->pluck('name');
                            }

                            // 進行交集比對
                            return $allPermissions->intersect($options->keys())->values()->toArray();
                        })
                ])
                ->collapsible();
        }

        return $components;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('基本屬性')
                ->schema(static::getBasicFormSchema())
                ->columns(3),

            Forms\Components\Grid::make()
                ->schema(static::getShieldFormComponents())
                ->columnSpanFull(),
        ]);
    }

    // --- 以下為原本的功能方法，確保與租戶隔離一致 ---

    protected static function getBasicFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('角色名稱')
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: function (Unique $rule) {
                        $tenantId = Filament::getTenant()?->id;
                        $teamColumn = config('permission.column_names.team_foreign_key') ?? 'team_id';
                        return $tenantId ? $rule->where($teamColumn, $tenantId) : $rule->whereNull($teamColumn);
                    }
                )
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('guard_name')
                ->label('防護機制 (Guard)')
                ->default(Utils::getFilamentAuthGuard())
                ->nullable(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $tenant = Filament::getTenant();
        if ($tenant) {
            $query->where(function (Builder $q) use ($tenant) {
                $q->where('team_id', $tenant->id)->orWhereNull('team_id');
            });
        }
        return $query;
    }

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('角色名稱')->badge(),
                Tables\Columns\TextColumn::make('permissions_count')->label('權限數量')->counts('permissions'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function isScopedToTenant(): bool
    {
        return Filament::getTenant() !== null;
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }
}