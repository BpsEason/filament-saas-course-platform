<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Filament\App\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    /**
     * 設定頂部操作按鈕
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('建立新課程')
                ->icon('heroicon-m-plus'),
        ];
    }

    /**
     * 🚀 架構師核心修正：自定義表格查詢邏輯 (Query Scoping)
     * 確保多租戶環境下的「權限隔離」
     */
    protected function getTableQuery(): ?Builder
    {
        $user = auth()->user();

        // 獲取基礎查詢（已包含 Filament 預設的租戶隔離）
        $query = parent::getTableQuery();

        /**
         * 💡 業務邏輯拆解：
         * 1. 如果是 Admin 角色，可以看到該租戶的所有課程。
         * 2. 如果只是普通老師，則額外限制 user_id 必須為自己。
         */
        if ($user->hasRole('admin')) {
            return $query;
        }

        // 限制僅顯示自己創建的課程
        return $query->where('user_id', $user->id);
    }
}
