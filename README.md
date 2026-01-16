# Filament SaaS 課程平台 - 體驗極速後台開發

本專案是一個基於 **Filament v3** 打造的**多租戶 (Multi-Tenancy) 線上課程 SaaS 平台**。

它的核心目標不僅是展示一個功能完整的課程系統，更是要示範如何**利用 Filament 的強大能力，以驚人的速度建構複雜、美觀且功能強大的管理後台**。

---

## 🚀 為何選擇 Filament？- 極速開發的秘密

傳統後台開發需要手寫大量 CRUD、表單、表格與前端互動邏輯。Filament 將這一切都變成了簡單的 PHP 類別配置。

-   **聲明式介面 (Declarative UI)**：忘掉 Blade 模板和 JavaScript 吧！你只需要在 PHP 中定義表單欄位和表格列，Filament 會自動生成對應的 Livewire 組件。

-   **資源即 CRUD (Resource = CRUD)**：一個 `Resource` 類別就等於一個完整的 CRUD 功能模組。執行 `php artisan make:filament-resource Course`，你就立即擁有課程的列表、新增、編輯、刪除頁面。

-   **豐富的內建組件**：從文字輸入、檔案上傳、日期選擇器到複雜的 `Repeater` 和 `Builder`，Filament 提供了大量現成的表單和表格組件，開箱即用。

-   **無縫整合生態系**：與 Laravel 的 Eloquent、Policy、Validation 完美結合，並可輕鬆整合 Spatie Permission、Media Library 等熱門套件。

---

## 🛠️ 快速開始 (Quick Start)

1.  **基礎安裝**

    ```bash
    git clone https://github.com/BpsEason/filament-saas-course-platform.git
    cd filament-saas-course-platform
    composer install && npm install
    cp .env.example .env && php artisan key:generate
    ```

2.  **設定資料庫並初始化**
    在 `.env` 中設定好你的資料庫連線，然後執行：

    ```bash
    # 這一步會建立所有資料表並植入豐富的測試資料
    php artisan migrate --seed
    ```

3.  **啟動伺服器**
    ```bash
    npm run dev
    # 開啟另一個終端
    php artisan serve
    ```

---

## 👥 預設帳號（開發測試用）

| 身份        | 信箱                 | 密碼       | 後台網址                      |
| ----------- | -------------------- | ---------- | ----------------------------- |
| Super Admin | `admin@system.com`   | `password` | `http://localhost:8000/admin` |
| NTU 管理員  | `admin@ntu.edu.tw`   | `password` | `http://localhost:8000/app`   |
| NTU 教師    | `teacher@ntu.edu.tw` | `password` | `http://localhost:8000/app`   |
| NTU 學生    | `student@ntu.edu.tw` | `password` | `http://localhost:8000/app`   |

---

## ⚡ Filament 開發實戰 (Filament in Action)

### 1. 剖析 `CourseResource` - Filament 的核心

`CourseResource` 是本專案最核心的範例。它展示了如何組織一個複雜的管理介面。

-   **`app/Filament/App/Resources/CourseResource.php`**: 主控檔案，定義了路由、關聯、全局操作等。
-   **`app/Filament/App/Resources/CourseResource/Schemas/CourseForm.php`**: 專門用來定義課程的**表單 (Form)**。所有欄位如 `TextInput`、`MarkdownEditor`、`FileUpload` 都在這裡配置。
-   **`app/Filament/App/Resources/CourseResource/Tables/CoursesTable.php`**: 專門用來定義課程的**表格 (Table)**。所有列如 `TextColumn`、`IconColumn`、`BadgeColumn` 以及篩選器 `Filter` 都在這裡配置。

這種將 Form 和 Table 邏輯拆分到獨立類別的做法，是保持 `Resource` 檔案簡潔的最佳實踐。

### 2. 5 分鐘建立「教師管理」

想體驗 Filament 的速度嗎？讓我們來建立一個新的 `TeacherResource`。

1.  **執行 Artisan 指令**

    ```bash
    php artisan make:filament-resource Teacher --generate
    ```

    這個指令會自動建立 `app/Filament/App/Resources/TeacherResource.php` 以及相關的列表、新增、編輯頁面。

2.  **定義表格與表單**
    打開 `TeacherResource.php`，在 `form()` 和 `table()` 方法中加入欄位：

    ```php
    // In TeacherResource.php

    use Filament\Forms\Components\TextInput;
    use Filament\Tables\Columns\TextColumn;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('specialty')->label('專業領域'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('specialty'),
            ])
            ->filters([
                // ...
            ]);
    }
    ```

3.  **完成！**
    就是這麼簡單！現在登入租戶後台 (`/app`)，你就會在側邊欄看到「Teachers」選單，並且擁有一個功能齊全的 CRUD 管理介面。

### 3. 權限控制的優雅

Filament 與 `spatie/laravel-permission` 的整合天衣無縫。

-   **資源層級**：在 `CourseResource.php` 中，我們可以用 `canViewAny()` 來決定整個模組的顯示與否。
    ```php
    public static function canViewAny(): bool
    {
        // 檢查租戶方案 + 使用者權限
        return filament()->getTenant()->hasModule('courses')
            && auth()->user()->can('view_any_course');
    }
    ```
-   **操作層級**：`CreateAction`、`EditAction`、`DeleteAction` 等都可以鏈式呼叫 `can()` 方法來進行更細緻的權限判斷。

---

## 📂 專案重要結構

```text
app/
├── Filament/
│   ├── App/          # 租戶面板 (租戶管理員、教師、學生使用)
│   │   └── Resources/
│   │       ├── CourseResource/     # 【重點】展示 Form/Table 分離的最佳實踐
│   │       └── EnrollmentResource/
│   └── Resources/    # 中央面板 (Super Admin 使用)
│       └── TenantResource.php      # 管理租戶
├── Models/
│   ├── Tenant.php    # 核心租戶模型，帶有 plan_features
│   └── User.php      # 核心使用者模型
├── Policies/         # Eloquent Policies，與 Filament 權限無縫對接
database/
├── migrations/       # 租戶 (Tenant) 資料庫的遷移
└── seeders/          # 【重點】包含完整的測試資料生成邏輯
```

---

## 💡 常見問題 / 除錯提示

-   **權限不生效？**
    → 執行 `php artisan permission:cache-reset` 清除 Spatie Permission 的快取。
-   **修改了 Filament 程式碼但頁面沒變？**
    → Filament 會對自身組件進行快取，嘗試執行 `php artisan filament:cache-clear`。

---

## 🛣️ 未來展望

-   [ ] **支付系統 (Cashier)**：整合 Stripe Connect 實現租戶分潤。
-   [ ] **前台介面**：使用 Livewire 或 Inert.js/Vue 為學生建立一個漂亮的前台。
-   [ ] **報表與分析**：利用 Filament 的 `Widgets` 建立更多數據儀表板。

---

## 🤝 參與貢獻

歡迎 Issue、PR！

> **"Code is like a trail; let's make it a beautiful journey for the next developer."**
