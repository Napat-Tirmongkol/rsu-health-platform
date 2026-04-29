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

        :root {
            --shell-bg: #0f172a;
            --shell-border: rgba(255, 255, 255, 0.08);
            --shell-text: rgba(226, 232, 240, 0.88);
            --surface-strong: #0f172a;
            --accent: #10b981;
            --accent-soft: rgba(16, 185, 129, 0.14);
            --accent-border: rgba(16, 185, 129, 0.26);
            --sidebar-width: 22rem;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.12), transparent 24%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 26%),
                #e2e8f0;
            color: var(--surface-strong);
        }

        body[data-module="borrow"] {
            --accent: #0ea5e9;
            --accent-soft: rgba(14, 165, 233, 0.14);
            --accent-border: rgba(14, 165, 233, 0.24);
        }

        .shell-sidebar {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(2, 8, 23, 0.97)),
                var(--shell-bg);
            color: var(--shell-text);
            border-right: 1px solid var(--shell-border);
            box-shadow: 24px 0 60px rgba(15, 23, 42, 0.18);
        }

        .shell-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .shell-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.35);
            border-radius: 999px;
        }

        .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        .workspace-card {
            transition: 220ms ease;
        }

        .workspace-card:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.06);
        }

        .workspace-card.active {
            border-color: var(--accent-border);
            background: linear-gradient(135deg, var(--accent-soft), rgba(255, 255, 255, 0.04));
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.28);
        }

        .nav-link {
            transition: 200ms ease;
            border: 1px solid transparent;
            border-radius: 1.125rem;
            color: rgba(226, 232, 240, 0.8);
        }

        .nav-link:hover {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }

        .nav-link.active {
            border-color: var(--accent-border);
            background: linear-gradient(135deg, var(--accent-soft), rgba(255, 255, 255, 0.04));
            color: #fff;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.26);
        }

        .main-stage {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(241, 245, 249, 0.94));
            backdrop-filter: blur(14px);
        }

        .topbar-chip {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 25px rgba(148, 163, 184, 0.12);
        }

        @media (min-width: 768px) {
            .admin-shell {
                display: grid;
                grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
                transition: grid-template-columns 300ms cubic-bezier(0.22, 1, 0.36, 1);
            }
        }
    </style>
    @livewireStyles
</head>
@php
    $moduleRegistry = config('admin_modules.modules', []);
    $actionRegistry = collect(config('admin_modules.actions', []))
        ->flatMap(fn (array $group) => collect($group['actions'] ?? []))
        ->keyBy('key');
    $adminUser = Auth::guard('admin')->user();
    $currentModule = 'platform';
    $routeActionMap = [
        'admin.campaigns' => 'campaign.manage',
        'admin.time_slots' => 'campaign.manage',
        'admin.users' => 'campaign.manage',
        'admin.reports' => 'campaign.manage',
        'admin.bookings' => 'campaign.booking.manage',
        'admin.borrow_requests' => 'borrow.request.approve',
        'admin.inventory' => 'borrow.inventory.manage',
        'admin.walk_in_borrow' => 'borrow.inventory.manage',
        'admin.borrow_returns' => 'borrow.return.process',
        'admin.borrow_fines' => 'borrow.fine.collect',
        'admin.borrow_payments.receipt' => 'borrow.fine.collect',
    ];

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
    $currentModuleMeta = $moduleRegistry[$currentModule] ?? null;
    $headerLabel = $currentModuleMeta['name'] ?? 'Platform Admin';
    $headerDescription = match ($currentModule) {
        'campaign' => 'ดูแคมเปญ การจอง และการบริการผู้ป่วยโดยไม่หลุดบริบท',
        'borrow' => 'คุมคำขอยืม สต็อก การคืน และค่าปรับใน command center เดียว',
        default => 'สลับหลายระบบได้ชัดเจนโดยไม่ทำให้เมนูรกหรือหลงบริบท',
    };
@endphp
<body class="overflow-hidden" data-module="{{ $currentModule }}">
    <div
        x-data="{
            sidebarCollapsed: localStorage.getItem('admin-sidebar-collapsed') === 'true',
            mobileSidebarOpen: false,
            toggleSidebar() {
                this.sidebarCollapsed = ! this.sidebarCollapsed;
                localStorage.setItem('admin-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
            }
        }"
        :style="`--sidebar-width: ${sidebarCollapsed ? '6.5rem' : '22rem'}`"
        class="h-screen w-screen overflow-hidden"
    >
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm md:hidden"
            @click="mobileSidebarOpen = false"
        ></div>

        <aside
            x-show="mobileSidebarOpen"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="shell-sidebar fixed inset-y-0 left-0 z-50 flex w-[22rem] flex-col md:hidden"
            x-cloak
        >
            <div class="shell-scroll overflow-y-auto px-7 pb-7 pt-8">
                <div class="mb-8 flex items-center gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white text-slate-900 shadow-lg">
                        <i class="fa-solid fa-clinic-medical text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-lg font-black leading-tight text-white">RSU Medical Hub</h1>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.28em] text-slate-400">Flagship Admin</p>
                    </div>
                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition-all hover:bg-white/10 hover:text-white"
                        title="ปิดเมนู"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="shell-card mb-8 rounded-[2rem] p-4">
                    <div class="px-2 pb-4 pt-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">Workspace Switcher</p>
                        <p class="mt-3 text-sm font-bold leading-relaxed text-slate-300">
                            เลือกโหมดงานให้ตรงกับบริบทที่กำลังดูอยู่ เพื่อให้ทีมคลินิกสลับหลายระบบได้โดยไม่หลงเมนู
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($moduleCards as $module)
                            <a href="{{ route($module['route']) }}" class="workspace-card {{ $module['active'] ? 'active' : 'shell-card' }} block rounded-[1.5rem] p-4">
                                <div class="flex gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $module['active'] ? 'bg-white text-slate-900' : 'bg-white/8 text-slate-200' }}">
                                        <i class="fa-solid {{ $module['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-[0.24em] {{ $module['active'] ? 'text-white/70' : 'text-slate-500' }}">{{ $module['name'] }}</p>
                                        <h4 class="mt-1 text-sm font-black {{ $module['active'] ? 'text-white' : 'text-slate-100' }}">{{ $module['label'] }}</h4>
                                        <p class="mt-1 text-xs font-bold leading-relaxed {{ $module['active'] ? 'text-white/72' : 'text-slate-400' }}">{{ $module['description'] }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <nav class="space-y-7">
                    @foreach ($navigationSections as $section)
                        <section>
                            <p class="mb-3 px-3 text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">{{ $section['title'] }}</p>

                            <div class="space-y-2">
                                @foreach ($section['items'] as $item)
                                    @php
                                        $linkedModule = null;
                                        if (in_array($item['route'], ['admin.workspace.campaign', 'admin.campaigns', 'admin.bookings', 'admin.time_slots', 'admin.users', 'admin.reports'], true)) {
                                            $linkedModule = 'campaign';
                                        } elseif (in_array($item['route'], ['admin.workspace.borrow', 'admin.borrow_requests', 'admin.inventory', 'admin.borrow_returns', 'admin.borrow_fines', 'admin.walk_in_borrow', 'admin.borrow_payments.receipt'], true)) {
                                            $linkedModule = 'borrow';
                                        }

                                        $requiresPlatformAccess = in_array($item['route'], ['admin.system_admins', 'admin.system_settings'], true);
                                        $requiredAction = $routeActionMap[$item['route']] ?? null;
                                    @endphp

                                    @if (
                                        (! $requiresPlatformAccess || ! $adminUser || $adminUser->hasFullPlatformAccess())
                                        && (! $linkedModule || ! $adminUser || $adminUser->hasModuleAccess($linkedModule))
                                        && (! $requiredAction || ! $adminUser || $adminUser->hasActionAccess($requiredAction))
                                    )
                                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }} flex items-center gap-3 px-4 py-3.5 text-sm font-bold">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs($item['route']) ? 'bg-white/10 text-white' : 'bg-white/5 text-slate-300' }}">
                                                <i class="fa-solid {{ $item['icon'] }}"></i>
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            @if ($requiredAction && isset($actionRegistry[$requiredAction]) && $currentModule !== 'platform')
                                                <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] {{ request()->routeIs($item['route']) ? 'text-white/70' : 'text-slate-400' }}">
                                                    {{ $actionRegistry[$requiredAction]['label'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-white/5 p-7">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 py-4 text-sm font-bold text-slate-200 transition-all hover:border-rose-300/30 hover:bg-rose-500 hover:text-white">
                        <i class="fa-solid fa-power-off"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-shell h-screen w-screen overflow-hidden">
            <aside
                :class="sidebarCollapsed ? 'w-[6.5rem]' : 'w-[22rem]'"
                class="shell-sidebar hidden h-screen w-full flex-col transition-all duration-300 ease-out md:flex"
            >
                <div class="shell-scroll overflow-y-auto px-7 pb-7 pt-8">
                    <div :class="sidebarCollapsed ? 'mb-6 justify-center' : 'mb-8'" class="flex items-center gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white text-slate-900 shadow-lg">
                            <i class="fa-solid fa-clinic-medical text-lg"></i>
                        </div>
                        <div x-show="! sidebarCollapsed" x-transition.opacity.duration.200ms>
                            <h1 class="text-lg font-black leading-tight text-white">RSU Medical Hub</h1>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.28em] text-slate-400">Flagship Admin</p>
                        </div>
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="ml-auto hidden h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition-all hover:bg-white/10 hover:text-white lg:inline-flex"
                            :title="sidebarCollapsed ? 'ขยายเมนู' : 'ย่อเมนู'"
                        >
                            <i class="fa-solid" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                        </button>
                    </div>

                    <div x-show="! sidebarCollapsed" x-transition.opacity.duration.200ms class="shell-card mb-8 rounded-[2rem] p-4">
                        <div class="px-2 pb-4 pt-1">
                            <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">Workspace Switcher</p>
                            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-300">
                                เลือกโหมดงานให้ตรงกับบริบทที่กำลังดูอยู่ เพื่อให้ทีมคลินิกสลับหลายระบบได้โดยไม่หลงเมนู
                            </p>
                        </div>

                        <div class="space-y-3">
                            @foreach ($moduleCards as $module)
                                <a href="{{ route($module['route']) }}" class="workspace-card {{ $module['active'] ? 'active' : 'shell-card' }} block rounded-[1.5rem] p-4">
                                    <div class="flex gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $module['active'] ? 'bg-white text-slate-900' : 'bg-white/8 text-slate-200' }}">
                                            <i class="fa-solid {{ $module['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-black uppercase tracking-[0.24em] {{ $module['active'] ? 'text-white/70' : 'text-slate-500' }}">{{ $module['name'] }}</p>
                                            <h4 class="mt-1 text-sm font-black {{ $module['active'] ? 'text-white' : 'text-slate-100' }}">{{ $module['label'] }}</h4>
                                            <p class="mt-1 text-xs font-bold leading-relaxed {{ $module['active'] ? 'text-white/72' : 'text-slate-400' }}">{{ $module['description'] }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <nav class="space-y-7">
                        @foreach ($navigationSections as $section)
                            <section>
                                <p x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms class="mb-3 px-3 text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">{{ $section['title'] }}</p>

                                <div class="space-y-2">
                                    @foreach ($section['items'] as $item)
                                        @php
                                            $linkedModule = null;
                                            if (in_array($item['route'], ['admin.workspace.campaign', 'admin.campaigns', 'admin.bookings', 'admin.time_slots', 'admin.users', 'admin.reports'], true)) {
                                                $linkedModule = 'campaign';
                                            } elseif (in_array($item['route'], ['admin.workspace.borrow', 'admin.borrow_requests', 'admin.inventory', 'admin.borrow_returns', 'admin.borrow_fines', 'admin.walk_in_borrow', 'admin.borrow_payments.receipt'], true)) {
                                                $linkedModule = 'borrow';
                                            }

                                            $requiresPlatformAccess = in_array($item['route'], ['admin.system_admins', 'admin.system_settings'], true);
                                            $requiredAction = $routeActionMap[$item['route']] ?? null;
                                        @endphp

                                        @if (
                                            (! $requiresPlatformAccess || ! $adminUser || $adminUser->hasFullPlatformAccess())
                                            && (! $linkedModule || ! $adminUser || $adminUser->hasModuleAccess($linkedModule))
                                            && (! $requiredAction || ! $adminUser || $adminUser->hasActionAccess($requiredAction))
                                        )
                                            <a
                                                href="{{ route($item['route']) }}"
                                                class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }} flex items-center gap-3 px-4 py-3.5 text-sm font-bold"
                                                :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                                                title="{{ $item['label'] }}"
                                            >
                                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs($item['route']) ? 'bg-white/10 text-white' : 'bg-white/5 text-slate-300' }}">
                                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                                </span>
                                                <span x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                                @if ($requiredAction && isset($actionRegistry[$requiredAction]) && $currentModule !== 'platform')
                                                    <span x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] {{ request()->routeIs($item['route']) ? 'text-white/70' : 'text-slate-400' }}">
                                                        {{ $actionRegistry[$requiredAction]['label'] }}
                                                    </span>
                                                @endif
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-white/5 p-7">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" :class="sidebarCollapsed ? 'px-0' : ''" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5 py-4 text-sm font-bold text-slate-200 transition-all hover:border-rose-300/30 hover:bg-rose-500 hover:text-white">
                            <i class="fa-solid fa-power-off"></i>
                            <span x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms>ออกจากระบบ</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="main-stage flex min-w-0 w-full flex-1 flex-col overflow-hidden">
                <header class="w-full border-b border-slate-200/80 bg-white/88 px-8 py-5 backdrop-blur-xl xl:px-10">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    @click="mobileSidebarOpen = true"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition-all hover:bg-slate-50 md:hidden"
                                    title="เปิดเมนู"
                                >
                                    <i class="fa-solid fa-bars"></i>
                                </button>
                                <span class="topbar-chip inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">{{ $headerLabel }}</span>
                                <span class="text-xs font-bold uppercase tracking-[0.24em] text-slate-400">{{ $currentModule === 'platform' ? 'Platform Home' : 'Workspace Context' }}</span>
                            </div>
                            <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h3>
                            <p class="mt-2 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">{{ $headerDescription }}</p>
                        </div>

                        <div class="topbar-chip flex items-center gap-4 rounded-[1.5rem] px-4 py-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-black leading-none text-slate-900">{{ $adminUser->name ?? 'Administrator' }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ $adminUser?->hasFullPlatformAccess() ? 'Full Platform Access' : 'Workspace Admin' }}</p>
                            </div>
                            <div class="h-12 w-12 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($adminUser->name ?? 'A') }}&background=0f172a&color=fff" class="h-full w-full object-cover" alt="Admin avatar">
                            </div>
                        </div>
                    </div>
                </header>

                <div class="shell-scroll w-full flex-1 overflow-y-auto px-6 py-6 lg:px-8 lg:py-8 xl:px-10 xl:py-10">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
