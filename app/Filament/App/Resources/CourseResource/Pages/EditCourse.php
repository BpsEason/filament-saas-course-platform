<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Filament\App\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * 🚀 架構師優化：權限橫向越權防護 (Insecure Direct Object Reference)
     * 確保只有課程擁有者或管理員可以進入編輯頁面
     */
    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        $record = $this->getRecord();

        // 如果不是超級管理員，且這門課不是該使用者創建的，則禁止訪問
        if (! $user->hasRole('admin') && $record->user_id !== $user->id) {
            abort(403, '您沒有權限編輯此課程');
        }

        parent::authorizeAccess();
    }

    /**
     * 設定頂部操作按鈕
     */
    protected function getHeaderActions(): array
    {
        return [
            // 查看前台按鈕（身為 Vlog 攝影師，隨時預覽成片效果是很重要的）
            Actions\Action::make('view_live')
                ->label('查看預覽')
                ->color('gray')
                ->icon('heroicon-m-eye')
                ->url(fn(Model $record) => route('courses.show', $record->slug), shouldOpenInNewTab: true),

            Actions\DeleteAction::make(),

            // 只有管理員可以看到強制刪除與恢復按鈕
            Actions\ForceDeleteAction::make()
                ->visible(fn() => Auth::user()->hasRole('admin')),

            Actions\RestoreAction::make()
                ->visible(fn() => Auth::user()->hasRole('admin')),
        ];
    }

    /**
     * 🚀 編輯完成後導回列表頁面
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * 成功修改後的通知訊息
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return '課程內容已更新';
    }
}
