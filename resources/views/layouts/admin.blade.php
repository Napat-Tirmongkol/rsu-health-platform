<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - RSU Medical Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8fafc;
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
        }

        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 1rem;
        }

        .nav-link:hover {
            background-color: rgba(46, 158, 99, 0.05);
            color: #2e9e63;
        }

        .nav-link.active {
            background-color: #2e9e63;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(46, 158, 99, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
    @livewireStyles
</head>
@php
    $moduleRegistry = config('admin_modules.modules', []);
    $adminUser = Auth::guard('admin')->user();
    $currentModule = 'platform';

    foreach ($moduleRegistry as $moduleKey => $module) {
        if (collect($module['patterns'] ?? [])->contains(fn ($pattern) => request()->routeIs($pattern))) {
            $currentModule = $moduleKey;
            break;
        }
    }

    $navigationSections = config("admin_modules.sections.{$currentModule}", config('admin_modules.sections.platform', []));
    $moduleCards = collect($moduleRegistry)
        ->filter(fn ($module, $key) => ! $adminUser || $adminUser->hasModuleAccess($key))
        ->map(function ($module, $key) use ($currentModule) {
            return array_merge($module, [
                'key' => $key,
                'active' => $currentModule === $key,
            ]);
        });
@endphp
<body class="overflow-hidden">
    <div class="flex h-screen overflow-hidden">
        <aside class="glass-sidebar z-50 flex w-72 flex-shrink-0 flex-col">
            <div class="custom-scrollbar overflow-y-auto p-8">
                <div class="mb-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2e9e63] text-white shadow-lg shadow-green-200">
                        <i class="fa-solid fa-clinic-medical"></i>
                    </div>
                    <div>
                        <h2 class="mb-1 font-black leading-none text-slate-800">RSU Medical</h2>
                        <p class="text-[10px] font-bold uppercase leading-none tracking-widest text-slate-400">Platform Admin</p>
                    </div>
                </div>

                <div class="mb-8 space-y-3 rounded-[1.75rem] border border-slate-100 bg-white/70 p-3 shadow-sm shadow-slate-100/80">
                    <div class="px-3 pt-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Workspace Switcher</p>
                        <p class="mt-2 text-xs font-bold leading-relaxed text-slate-500">เลือกโมดูลให้ตรงกับงานที่กำลังทำอยู่ แล้วระบบจะจัดเมนูให้เหมาะกับบริบทนั้นทันที</p>
                    </div>

                    @foreach ($moduleCards as $module)
                        <a href="{{ route($module['route']) }}" class="block rounded-2xl border px-4 py-3 transition-all {{ $module['active'] ? 'border-emerald-200 bg-emerald-50 shadow-sm shadow-emerald-100/70' : 'border-transparent bg-slate-50/80 hover:border-slate-200 hover:bg-white' }}">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $module['active'] ? 'bg-emerald-500 text-white' : 'bg-white text-slate-500 shadow-sm' }}">
                                    <i class="fa-solid {{ $module['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] {{ $module['active'] ? 'text-emerald-600' : 'text-slate-400' }}">{{ $module['name'] }}</p>
                                    <h4 class="mt-1 text-sm font-black text-slate-800">{{ $module['label'] }}</h4>
                                    <p class="mt-1 text-xs font-bold leading-relaxed text-slate-500">{{ $module['description'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <nav class="space-y-8">
                    @foreach ($navigationSections as $section)
                        <div class="space-y-2">
                            <p class="mb-4 ml-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $section['title'] }}</p>

                            @foreach ($section['items'] as $item)
                                @php
                                    $linkedModule = null;
                                    if (in_array($item['route'], ['admin.workspace.campaign', 'admin.campaigns', 'admin.bookings', 'admin.time_slots', 'admin.users', 'admin.reports'], true)) {
                                        $linkedModule = 'campaign';
                                    } elseif (in_array($item['route'], ['admin.workspace.borrow', 'admin.borrow_requests', 'admin.inventory', 'admin.borrow_returns', 'admin.borrow_fines', 'admin.walk_in_borrow', 'admin.borrow_payments.receipt'], true)) {
                                        $linkedModule = 'borrow';
                                    }

                                    $requiresPlatformAccess = in_array($item['route'], ['admin.system_admins', 'admin.system_settings'], true);
                                @endphp

                                @if ((! $requiresPlatformAccess || ! $adminUser || $adminUser->hasFullPlatformAccess()) && (! $linkedModule || ! $adminUser || $adminUser->hasModuleAccess($linkedModule)))
                                    <a href="{{ route($item['route']) }}" class="nav-link flex items-center gap-3 px-5 py-3.5 text-sm font-bold {{ request()->routeIs($item['route']) ? 'active' : 'text-slate-500' }}">
                                        <i class="fa-solid {{ $item['icon'] }} w-5"></i>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-slate-50 p-8">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-50 py-4 text-sm font-bold text-rose-500 shadow-sm transition-all hover:bg-rose-500 hover:text-white">
                        <i class="fa-solid fa-power-off"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="flex h-20 flex-shrink-0 items-center justify-between border-b border-slate-100/50 bg-white/50 px-10 backdrop-blur-md">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">{{ $currentModule === 'platform' ? 'Platform Home' : ($moduleRegistry[$currentModule]['name'] ?? 'Admin Workspace') }}</p>
                    <h3 class="mt-1 text-lg font-black tracking-tight text-slate-800">{{ $title ?? 'Dashboard' }}</h3>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex flex-col text-right">
                        <p class="mb-1 text-sm font-black leading-none text-slate-800">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[10px] font-bold uppercase leading-none tracking-widest text-emerald-500">Admin</p>
                    </div>
                    <div class="h-12 w-12 overflow-hidden rounded-2xl border-2 border-white bg-slate-100 shadow-sm">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('admin')->user()->name ?? 'A') }}&background=2e9e63&color=fff" class="h-full w-full object-cover" alt="Admin avatar">
                    </div>
                </div>
            </header>

            <div class="custom-scrollbar flex-1 overflow-y-auto p-10">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
