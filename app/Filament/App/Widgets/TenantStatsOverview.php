<?php

namespace App\Filament\App\Widgets;

use App\Models\Enrollment;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TenantStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        if (!$tenant) {
            return [];
        }

        $totalRevenue = Enrollment::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->sum('amount');

        $studentsCount = $tenant->users()->count();

        $todayEnrollments = Enrollment::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // 格式化金額顯示
        $formattedRevenue = $totalRevenue >= 10000
            ? number_format($totalRevenue / 1000, 1) . 'k'
            : number_format($totalRevenue);

        return [
            Stat::make('總營收', 'TWD ' . $formattedRevenue)
                ->description('累積報名總額')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([$totalRevenue * 0.5, $totalRevenue * 0.8, $totalRevenue])
                ->color('success'),

            Stat::make('學生總數', $studentsCount . ' 人')
                ->description('當前學校註冊學生')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('今日新報名', $todayEnrollments . ' 筆')
                ->description('過去 24 小時')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
        ];
    }
}
