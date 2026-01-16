<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// 🚀 關鍵導入：這是讓 Shield 介面能抓到這個資源的「天線」
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

// 🚀 導入外部的表單與表格配置
use App\Filament\App\Resources\CourseResource\Schemas\CourseForm;
use App\Filament\App\Resources\CourseResource\Tables\CoursesTable;

class CourseResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = '課程管理';
    protected static ?string $modelLabel = '課程';
    protected static ?string $pluralModelLabel = '課程列表';
    protected static ?string $navigationGroup = '教學管理';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $slug = 'courses';

    /**
     * 🚀 多租戶核心：
     * 設為 true 才能確保老師登入後「只能看見自己學校的課」
     */
    protected static bool $isScopedToTenant = true;

    /**
     * 🚀 核心控制：Admin 開關與使用者權限的交集
     * 只有當租戶具備 'courses' 模組，且使用者擁有 'view_any_course' 權限時，選單才會顯示。
     */
    public static function canViewAny(): bool
    {
        $tenant = filament()->getTenant();

        // 1. 檢查當前租戶是否已在 Admin 面板被授權 'courses' 功能
        $isModuleEnabled = $tenant && $tenant->hasModule('courses');

        // 2. 結合 Spatie Shield 的權限判斷
        return $isModuleEnabled && auth()->user()->can('view_any_course');
    }

    /**
     * 🚀 權限核心：
     * 定義這組資源在權限勾選頁面（Shield）中會出現哪些功能開關
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'publish', // 自定義功能：發佈課程
        ];
    }

    public static function form(Form $form): Form
    {
        return CourseForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}