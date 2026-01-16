<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // 1. 🚀 多租戶隔離核心
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // 2. 核心關聯
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('學生ID');
            $table->foreignId('course_id')->constrained()->cascadeOnDelete()->comment('課程ID');

            // 3. 🚀 財務紀錄 (對齊 Widget 的 amount 欄位名稱)
            // 這裡將 paid_amount 改為 amount 以對齊我們先前 Widget 的 sum('amount') 邏輯
            $table->decimal('amount', 10, 2)->default(0)->comment('實際支付金額');
            $table->string('currency', 3)->default('TWD');

            // 4. 狀態控管
            // pending: 待繳費, completed: 已完成, refunded: 已退款
            $table->string('status')->default('completed')->index();

            // 5. 學習進度
            $table->unsignedTinyInteger('progress_rate')->default(0)->comment('學習進度百分比');

            // 6. 時間戳記
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('課程觀看期限');

            $table->timestamps();
            $table->softDeletes();

            // 7. 🚀 業務邏輯防護：同一個租戶內，同一個學生對同一門課只能有一筆「有效」報名
            // 這能防止重複點擊導致的重複計費或重複統計
            $table->unique(['tenant_id', 'user_id', 'course_id'], 'unique_tenant_enrollment');

            // 8. 索引優化
            $table->index(['tenant_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
