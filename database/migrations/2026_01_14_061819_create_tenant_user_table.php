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
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();

            // 🚀 核心外鍵設定
            // constrained()：自動連結到 tenants 與 users 資料表
            // cascadeOnDelete()：當 User 或 Tenant 刪除時，此關聯會自動清理，防止垃圾資料
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 🔒 唯一性約束：確保同一個使用者不會在同一個租戶中重複關聯
            $table->unique(['tenant_id', 'user_id'], 'idx_tenant_user_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_user');
    }
};
