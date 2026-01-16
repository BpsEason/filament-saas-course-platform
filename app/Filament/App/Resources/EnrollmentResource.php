<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

// 🚀 關鍵導入：讓 Shield 掃描到此資源
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class EnrollmentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = '報名管理';
    protected static ?string $modelLabel = '報名';
    protected static ?string $pluralModelLabel = '報名列表';

    // 🚀 設定選單分組
    protected static ?string $navigationGroup = '教務管理';
    protected static ?int $navigationSort = 2;

    /**
     * 🚀 SaaS 核心：開啟租戶隔離
     * 確保資料查詢會自動加上 tenant_id 篩選
     */
    protected static bool $isScopedToTenant = true;

    /**
     * 🚀 核心控制：Admin 總控開關 + 使用者權限判斷
     * 只有租戶開通了 'enrollments' 模組，且使用者有 'view_any_enrollment' 權限才會顯示。
     */
    public static function canViewAny(): bool
    {
        $tenant = filament()->getTenant();

        // 檢查租戶模型中是否有對應的模組開關
        $isModuleEnabled = $tenant && $tenant->hasModule('enrollments');

        return $isModuleEnabled && auth()->user()->can('view_any_enrollment');
    }

    /**
     * 🚀 權限核心：讓 Shield 生成對應的權限開關
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('報名資訊')
                    ->description('管理學生與課程的關聯')
                    ->schema([
                        // 🚀 優化：僅列出目前租戶下的學生 (User)
                        Forms\Components\Select::make('user_id')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                // 💡 這裡會自動受到 tenant 作用域限制
                                modifyQueryUsing: fn(Builder $query) => $query->whereHas('tenants', fn($q) => $q->where('tenants.id', filament()->getTenant()->id))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('選擇學生'),

                        // 🚀 優化：僅列出目前租戶下的課程 (Course)
                        Forms\Components\Select::make('course_id')
                            ->relationship(
                                name: 'course',
                                titleAttribute: 'title',
                                // 修正：在多租戶模式下，filament() 會自動過濾，但手動加上更安全
                                modifyQueryUsing: fn(Builder $query) => $query->where('tenant_id', filament()->getTenant()->id)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('選擇課程'),

                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->prefix('TWD')
                            ->label('實收金額'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => '學習中',
                                'completed' => '已完課',
                                'refunded' => '已退款',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false)
                            ->label('狀態'),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('報名時間')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('學生姓名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('課程名稱')
                    ->limit(20)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'info',
                        'completed' => 'success',
                        'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => '學習中',
                        'completed' => '已完課',
                        'refunded' => '已退款',
                        default => $state,
                    })
                    ->label('狀態'),

                Tables\Columns\TextColumn::make('progress_rate')
                    ->numeric()
                    ->formatStateUsing(fn($state) => "{$state}%")
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    })
                    ->label('進度'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('TWD')
                    ->label('金額')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => '學習中',
                        'completed' => '已完課',
                        'refunded' => '已退款',
                    ])
                    ->label('狀態過濾'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}