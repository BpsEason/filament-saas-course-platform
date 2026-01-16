<?php

use Illuminate\Support\Facades\Route;
use App\Models\Course;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/**
 * 1. 門面：前台首頁 (Index Page)
 * 🚀 將原本的 welcome 替換為展示所有「已啟用的課程」
 */
Route::get('/', function () {
    // 透過 latest() 確保最新的課程排在最前面，就像 Vlog 頻道最新的影片一樣
    $courses = Course::where('is_active', true)
        ->latest()
        ->get();

    return view('index', compact('courses'));
});

/**
 * 2. 核心：前台課程模組 (Course Module)
 */
Route::group(['prefix' => 'courses', 'as' => 'courses.'], function () {

    // 📍 課程詳情頁面
    // 使用 {slug} 提升 SEO，這就像是在山林步道中標示清楚的景點路牌
    Route::get('/{slug}', function ($slug) {

        $course = Course::with(['tenant', 'user']) // 修正：關聯通常是 user (老師) 而非 teacher
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('courses.show', compact('course'));
    })->name('show');

    /**
     * 📍 報名處理 (Enrollment Handling)
     * 使用 auth middleware 確保只有登入的使用者能報名
     */
    Route::post('/{course}/enroll', function (Course $course) {

        // 🚀 架構師的安全檢查：確保課程是啟用的才能報名
        if (!$course->is_active) {
            return back()->with('error', '該課程目前無法報名');
        }

        // 💡 這裡是未來對接 EnrollmentController 的地方
        // 暫時使用 Session Flash Message 提供操作反饋
        return back()->with('success', "您已成功申請報名「{$course->title}」！系統正在處理您的資格。");
    })->middleware(['auth'])->name('enroll');
});

Route::get('/test-my-permissions', function () {
    if (!auth()->check()) return '請先登入';
    $user = auth()->user();

    echo "<h1>🧪 原始資料庫診斷</h1>";
    echo "<b>User ID:</b> " . $user->id . "<br>";
    echo "<b>User Model Class:</b> " . get_class($user) . "<br>";
    echo "<hr>";

    // 🚀 關鍵 1：直接看 model_has_roles 表
    $rawRoles = DB::table('model_has_roles')
        ->where('model_id', $user->id)
        ->get();

    echo "<h3>📊 model_has_roles 原始紀錄:</h3>";
    if ($rawRoles->isEmpty()) {
        echo "<span style='color:red;'>警告：資料庫裡根本沒有這個 User ID 的角色紀錄！</span>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>role_id</th><th>model_type</th><th>model_id</th><th>team_id</th></tr>";
        foreach ($rawRoles as $row) {
            $isTypeMatch = ($row->model_type === get_class($user)) ? '✅' : '❌';
            echo "<tr>
                    <td>{$row->role_id}</td>
                    <td>{$row->model_type} {$isTypeMatch}</td>
                    <td>{$row->model_id}</td>
                    <td>" . ($row->team_id ?? 'NULL') . "</td>
                  </tr>";
        }
        echo "</table>";
        echo "<small>* ❌ 代表 model_type 與當前 User 模型不一致</small>";
    }

    // 🚀 關鍵 2：看看 Role 資料表長怎樣
    echo "<h3>📜 Role 資料表清單:</h3>";
    $roles = DB::table('roles')->get();
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>id</th><th>name</th><th>guard_name</th><th>team_id</th></tr>";
    foreach ($roles as $r) {
        echo "<tr>
                <td>{$r->id}</td>
                <td>{$r->name}</td>
                <td>{$r->guard_name}</td>
                <td>" . ($r->team_id ?? 'NULL') . "</td>
              </tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h3>🔍 Spatie 套件判定:</h3>";
    setPermissionsTeamId(1); // 假設檢查台大
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    echo "<b>當前 Team ID:</b> " . getPermissionsTeamId() . "<br>";
    echo "<b>Spatie 判定角色:</b> " . $user->getRoleNames()->implode(', ') ?: '無角色';
});