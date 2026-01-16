<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>探索生成式 AI 的無限可能 - SaaS 課程平台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap');

        body {
            font-family: 'Noto Sans TC', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

    <header class="bg-indigo-900 text-white py-20 px-4 text-center relative overflow-hidden">
        <div class="max-w-4xl mx-auto relative z-10">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">探索生成式 AI 的無限可能</h1>
            <p class="text-xl text-indigo-100 mb-8">從 Laravel 全端開發到 AI 應用實踐，打造屬於你的未來技術棧。</p>
            <div class="flex justify-center gap-4">
                <a href="#courses"
                    class="bg-white text-indigo-900 px-8 py-3 rounded-full font-bold hover:bg-indigo-50 transition">開始學習</a>
                <a href="/admin"
                    class="border border-indigo-300 text-white px-8 py-3 rounded-full font-bold hover:bg-white/10 transition">進入後台</a>
            </div>
        </div>
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="10" cy="10" r="50" fill="white" />
                <circle cx="90" cy="90" r="50" fill="white" />
            </svg>
        </div>
    </header>

    <main id="courses" class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold mb-8 border-l-4 border-indigo-600 pl-4">精選推薦課程</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($courses as $course)
                <div
                    class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 group">
                    <div class="aspect-video bg-gray-200 overflow-hidden relative">
                        @if ($course->thumbnail)
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">暫無封面</div>
                        @endif
                        <div
                            class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-indigo-600">
                            熱門
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 group-hover:text-indigo-600 transition">{{ $course->title }}
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-2 mb-4">
                            {{ strip_tags($course->description) }}
                        </p>

                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-lg font-bold text-gray-900">TWD
                                ${{ number_format($course->price) }}</span>
                            <a href="{{ route('courses.show', $course->slug) }}"
                                class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-600 hover:text-white transition">
                                查看詳情
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 text-gray-400 italic">
                    目前還沒有發佈的課程，請至後台新增。
                </div>
            @endforelse
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; 2026 SaaS 課程平台 - 全端架構師帶路，走路也走進故事。
        </div>
    </footer>

</body>

</html>
