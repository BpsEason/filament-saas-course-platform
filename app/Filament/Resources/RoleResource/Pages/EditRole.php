<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    // 🚀 刪除原本的 mount() 方法！讓 Resource 裡的 formatStateUsing 運作。

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 抓取所有結尾是 Resource 的動態 Key 內容
        $this->permissions = collect($data)
            ->filter(fn($value, $key) => str_ends_with($key, 'Resource'))
            ->flatten()
            ->unique()
            ->filter();

        // 只回傳 Role 模型欄位
        return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
    }

    protected function afterSave(): void
    {
        $permissionModel = Utils::getPermissionModel();

        // 將權限名稱轉換為 Model 實體並同步
        $permissionModels = $this->permissions->map(function ($name) use ($permissionModel) {
            return $permissionModel::firstOrCreate([
                'name' => $name,
                'guard_name' => $this->data['guard_name'] ?? 'web',
            ]);
        });

        $this->record->syncPermissions($permissionModels);
    }
}