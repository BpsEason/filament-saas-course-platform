<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} - 課程詳情</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap');

        body {
            font-family: 'Noto Sans TC', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="text-xl font-bold text-indigo-600">SaaS 課程平台</a>
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-sm text-gray-600">你好, {{ auth()->user()->name }}</span>
                    <a href="/admin" class="text-sm text-gray-500 hover:text-indigo-600">進入後台</a>
                @else
                    <a href="/admin/login" class="text-sm text-indigo-600 font-medium">登入</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">

        @if (session('success'))
            <div
                class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 shadow-sm rounded-r-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">

            <div class="lg:w-2/3">
                <div class="rounded-2xl overflow-hidden shadow-lg bg-gray-200 aspect-video mb-6 ring-1 ring-gray-200">
                    @if ($course->thumbnail)
                        <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}"
                            class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>暫無封面圖片</span>
                        </div>
                    @endif
                </div>

                <h1 class="text-3xl font-bold mb-4">{{ $course->title }}</h1>

                <div class="flex items-center gap-4 mb-8 text-sm text-gray-600">
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-medium">精選課程</span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        發布於：{{ $course->created_at->format('Y-m-d') }}
                    </span>
                </div>

                <div class="prose prose-indigo max-w-none bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold mb-4 border-l-4 border-indigo-500 pl-3">課程介紹</h2>
                    <div class="text-gray-700 leading-relaxed">
                        {!! $course->description !!}
                    </div>
                </div>
            </div>

            <div class="lg:w-1/3">
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 sticky top-24">
                    <div class="text-3xl font-bold text-gray-900 mb-2">
                        TWD ${{ number_format($course->price) }}
                    </div>
                    <p class="text-gray-500 text-sm mb-6 font-medium">一次購買，永久觀看</p>

                    <form action="{{ route('courses.enroll', $course->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                                </path>
                            </svg>
                            立即報名參加
                        </button>
                    </form>

                    <div class="mt-6 space-y-4 text-sm text-gray-600">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>完整章節存取權限</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>支援行動裝置觀看</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-400 italic">由 {{ $course->tenant->name ?? '本平台' }} 提供技術支持</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>

</html>
