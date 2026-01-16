<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Facades\Filament;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // 🚀 關鍵 1：讓 Filament 自動辨識租戶關聯
    protected static ?string $tenantOwnershipRelationshipName = 'tenants';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = '使用者管理';
    protected static ?string $modelLabel = '使用者';
    protected static ?string $navigationGroup = '系統設定';

    /**
     * 🚀 關鍵 2：數據隔離邏輯 (Data Isolation)
     * 這會攔截所有的資料庫查詢，確保管理員只能看到所屬校區的人
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $tenant = Filament::getTenant();
        $currentUser = auth()->user();

        // 🚀 關鍵修正：判斷目前登入者的身份
        // 只有當「登入者不是 Super Admin」時，才執行排除 Super Admin 的邏輯
        if ($currentUser && ! $currentUser->hasRole('super_admin')) {
            $query->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereRaw('model_has_roles.model_id = users.id')
                    ->where('model_has_roles.model_type', \App\Models\User::class)
                    ->where('roles.name', 'super_admin');
            });
        }

        // 🚀 租戶隔離：校區管理員只能看到自己校區的人
        // 但如果你希望 Super Admin 在校區面板也能看到所有人，可以再加判斷
        if ($tenant) {
            $query->whereIn('users.id', function ($q) use ($tenant) {
                $q->select('user_id')
                    ->from('tenant_user')
                    ->where('tenant_id', $tenant->id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資料')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('密碼')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->rule(Password::default()),
                    ])->columns(2),

                Forms\Components\Section::make('權限與所屬租戶')
                    ->description('這將決定使用者可以進入哪間學校，以及擁有的權限。')
                    ->schema([
                        // 1. 租戶選擇：在校區面板時自動隱藏，因為不需要手動選
                        Forms\Components\Select::make('tenants')
                            ->label('所屬租戶 (學校)')
                            ->relationship('tenants', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->visible(fn() => Filament::getTenant() === null),

                        // 🚀 2. 角色選擇器：限定範圍，不讓校區管理員指派 Super Admin 角色
                        Forms\Components\Select::make('roles')
                            ->label('系統角色')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->dehydrated(false)
                            ->options(function () {
                                $tenantId = Filament::getTenant()?->id;
                                // 只能選擇「全域角色」或「當前校區專屬角色」
                                return Role::withoutGlobalScopes()
                                    ->where(function ($q) use ($tenantId) {
                                        $q->whereNull('team_id')
                                            ->when($tenantId, fn($query) => $query->orWhere('team_id', $tenantId));
                                    })
                                    ->where('name', '!=', 'super_admin') // 即使是 admin 也不准創 super_admin
                                    ->get()
                                    ->mapWithKeys(fn($role) => [
                                        $role->id => $role->name . ($role->team_id ? " (校區專屬)" : " (系統全域)")
                                    ])
                                    ->toArray();
                            })
                            ->formatStateUsing(function ($record) {
                                if (!$record) return [];
                                return \Illuminate\Support\Facades\DB::table('model_has_roles')
                                    ->where('model_id', $record->id)
                                    ->where('model_type', User::class)
                                    ->pluck('role_id')
                                    ->map(fn($id) => (string)$id)
                                    ->toArray();
                            })
                            ->saveRelationshipsUsing(function (User $record, $state) {
                                $newRoleIds = collect($state)->map(fn($id) => (int)$id)->filter()->unique()->toArray();
                                $rolesToAssign = Role::withoutGlobalScopes()->whereIn('id', $newRoleIds)->get();

                                \Illuminate\Support\Facades\DB::transaction(function () use ($record, $rolesToAssign) {
                                    \Illuminate\Support\Facades\DB::table('model_has_roles')
                                        ->where('model_id', $record->id)
                                        ->where('model_type', User::class)
                                        ->delete();

                                    $insertData = [];
                                    foreach ($rolesToAssign as $role) {
                                        $insertData[] = [
                                            'role_id'    => $role->id,
                                            'model_id'   => $record->id,
                                            'model_type' => User::class,
                                            'team_id'    => $role->team_id ?? Filament::getTenant()?->id,
                                        ];
                                    }

                                    if (!empty($insertData)) {
                                        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert($insertData);
                                    }
                                });

                                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('姓名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tenants.name')
                    ->label('所屬租戶')
                    ->badge()
                    ->color('info')
                    ->visible(fn() => Filament::getTenant() === null),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('角色')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenants')
                    ->label('依租戶篩選')
                    ->relationship('tenants', 'name')
                    ->visible(fn() => Filament::getTenant() === null),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}