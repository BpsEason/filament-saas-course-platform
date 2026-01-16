<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        // 🚀 修正 1：預先載入 tenants 關聯，避免 N+1 問題
        $users = User::where('id', '>', 1)->with('tenants')->get();

        if ($users->isEmpty()) {
            $this->command->warn('找不到可用的學生，請先執行 DatabaseSeeder 建立 User。');
            return;
        }

        foreach ($tenants as $tenant) {
            // 💡 為了保險，確保在操作該租戶數據時權限 Context 正確
            setPermissionsTeamId($tenant->id);

            $courses = Course::where('tenant_id', $tenant->id)->get();

            if ($courses->isEmpty()) {
                $this->command->warn("租戶 {$tenant->name} 內無課程，略過報名生成。");
                continue;
            }

            // 🚀 修正 2：先過濾出該租戶的學生集合，避免重複過濾
            $tenantStudents = $users->filter(fn($u) => $u->tenants->contains($tenant->id));

            if ($tenantStudents->isEmpty()) {
                $this->command->warn("租戶 {$tenant->name} 內無學生，略過報名生成。");
                continue;
            }

            $this->command->info("正在為 {$tenant->name} 生成報名數據...");

            for ($i = 0; $i < 50; $i++) {
                $course = $courses->random();
                $student = $tenantStudents->random();

                $randomDate = now()->subMonths(rand(0, 11))->subDays(rand(1, 28));

                Enrollment::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'user_id'   => $student->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'amount'       => $course->price,
                        'status'       => 'completed',
                        'created_at'   => $randomDate,
                        'updated_at'   => $randomDate,
                        'enrolled_at'  => $randomDate,
                    ]
                );
            }
        }

        // 執行完畢恢復全域 Context
        setPermissionsTeamId(null);
    }
}