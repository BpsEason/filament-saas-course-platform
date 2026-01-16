<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // 1. 租戶與擁有者關聯
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // 2. 課程內容基本欄位
            $table->string('title')->comment('課程名稱');
            $table->string('slug')->comment('URL 別名');
            $table->string('thumbnail')->nullable()->comment('課程封面圖路徑'); // 🚀 新增封面圖欄位
            $table->text('description')->nullable()->comment('課程描述');

            // 3. 管理與狀態欄位
            $table->boolean('is_active')->default(false)->index()->comment('發布狀態');
            $table->timestamp('published_at')->nullable()->comment('發布時間');

            // 4. 商業欄位 (建議改用 decimal 處理金額)
            $table->decimal('price', 10, 2)->default(0)->comment('課程售價');

            $table->timestamps();
            $table->softDeletes();

            // 🚀 複合唯一索引：確保同一學校內 Slug 不重複
            $table->unique(['tenant_id', 'slug']);
            // 🚀 查詢優化索引
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
