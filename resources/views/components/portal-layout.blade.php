@props(['title' => 'Portal'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - RSU Medical Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    @php($portalUser = Auth::guard('portal')->user())

    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-6 py-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="space-y-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-sky-500">RSU Medical Hub</p>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-950">Portal Console</h1>
                        <p class="text-sm font-bold text-slate-500">ศูนย์กลางสำหรับดูภาพรวมทุกคลินิก จัดการค่าระบบ และควบคุมบริการข้ามโมดูล</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <nav class="flex flex-wrap gap-2">
                        <a href="{{ route('portal.dashboard') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ request()->routeIs('portal.dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('portal.clinics') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ request()->routeIs('portal.clinics') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Clinics
                        </a>
                        <a href="{{ route('portal.chatbot.faqs') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ request()->routeIs('portal.chatbot.faqs*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Chatbot FAQs
                        </a>
                        <a href="{{ route('portal.chatbot.settings') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ request()->routeIs('portal.chatbot.settings*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Chatbot Settings
                        </a>
                        <a href="{{ route('portal.settings') }}" class="rounded-2xl px-4 py-2 text-sm font-black {{ request()->routeIs('portal.settings') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Settings
                        </a>
                    </nav>

                    <div class="flex items-center justify-between gap-4 rounded-[1.75rem] border border-slate-200 bg-white px-4 py-3 shadow-sm sm:min-w-[18rem]">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-black text-slate-950">{{ $portalUser?->name ?? 'Portal User' }}</div>
                            <div class="truncate text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Portal Access</div>
                        </div>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-slate-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{ $slot }}
    </div>
</body>
</html>
