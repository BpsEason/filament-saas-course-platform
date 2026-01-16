<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\HasTenants; // 可選，視你的架構而定

/**
 * Enrollment 模型 - 處理學生報名、支付與學習狀態
 */
class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 🚀 為了讓 Filament 自動處理租戶隔離，建議確保此欄位存在
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'course_id',
        'paid_amount',
        'currency',
        'status',
        'progress_rate',
        'enrolled_at',
        'completed_at',
        'expires_at',
    ];

    /**
     * 🚀 類型轉換：確保數據從資料庫取出時型別正確
     */
    protected $casts = [
        'paid_amount' => 'decimal:2',
        'progress_rate' => 'integer',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------- */
    /* 關係連結 (Relationships)                                                    */
    /* -------------------------------------------------------------------------- */

    /**
     * 屬於特定租戶 (學校/企業)
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 報名的學生 (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 所屬的課程
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /* -------------------------------------------------------------------------- */
    /* 業務邏輯 (Business Logic)                                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * 🚀 判斷報名是否有效 (學習權限判斷)
     * 考慮了：狀態必須為 active 或 completed，且未過期
     */
    public function isActive(): bool
    {
        $validStatuses = ['active', 'completed'];

        return in_array($this->status, $validStatuses) &&
            (is_null($this->expires_at) || $this->expires_at->isFuture());
    }

    /**
     * 🚀 標記為完成課程
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_rate' => 100,
            'completed_at' => now(),
        ]);
    }

    /**
     * 🚀 判斷是否為「付費報名」
     * 修正：使用 bcmod 或直接比較，確保 decimal 比較準確
     */
    public function isPaid(): bool
    {
        return (float) $this->paid_amount > 0;
    }

    /**
     * 🚀 Scope: 僅限已完成支付的統計 (用於 Widget)
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'completed');
    }
}
