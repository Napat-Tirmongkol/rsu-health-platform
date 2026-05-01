<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - RSU Medical Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-expanded-width: 16rem;
            --sidebar-collapsed-width: 5.5rem;
            --sidebar-current-width: 16rem;
            --brand-green: #2e9e63;
            --brand-green-deep: #2a8455;
            --brand-green-soft: #e8f8f0;
            --brand-green-border: #c7e8d5;
            --shell-bg: #e8f4ec;
            --shell-bg-strong: #f4faf6;
            --shell-border: #d6ebde;
            --text-strong: #0f172a;
            --text-body: #475569;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: var(--shell-bg);
            color: var(--text-strong);
        }

        .admin-shell {
            background:
                radial-gradient(circle at 12% 10%, rgba(46, 158, 99, 0.08) 0, transparent 340px),
                radial-gradient(circle at 88% 88%, rgba(110, 231, 183, 0.08) 0, transparent 280px),
                var(--shell-bg);
        }

        .admin-sidebar {
            background: #fff;
            border-right: 1.5px solid var(--brand-green-border);
            box-shadow: 2px 0 12px rgba(46, 158, 99, 0.07);
        }

        .topbar {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1.5px solid var(--brand-green-border);
            box-shadow: 0 2px 8px rgba(46, 158, 99, 0.05);
            backdrop-filter: blur(12px);
        }

        .main-stage {
            background: transparent;
        }

        .workspace-chip {
            border: 1.5px solid var(--shell-border);
            background: #fff;
            color: var(--text-body);
            transition: 180ms ease;
        }

        .workspace-chip:hover {
            background: #f0faf4;
            border-color: var(--brand-green-border);
            color: var(--brand-green-deep);
        }

        .workspace-chip.active {
            background: var(--brand-green-soft);
            border-color: var(--brand-green-border);
            color: var(--brand-green);
        }

        .nav-section-title {
            color: var(--text-muted);
            letter-spacing: .14em;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: .75rem;
            min-height: 3.25rem;
            border-radius: .95rem;
            color: #4b5563;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }

        .nav-link:hover {
            background: #f0faf4;
            color: #1a5c38;
        }

        .nav-link.active {
            background: #e8f8f0;
            color: var(--brand-green);
            font-weight: 700;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: -.65rem;
            top: .6rem;
            bottom: .6rem;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(180deg, #2e9e63, #6ee7b7);
            box-shadow: 0 4px 10px rgba(46, 158, 99, 0.3);
        }

        .nav-icon {
            display: flex;
            width: 2.15rem;
            height: 2.15rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: .75rem;
            background: transparent;
            color: inherit;
            transition: background .18s ease;
        }

        .nav-link:hover .nav-icon {
            background: #d6f0e2;
        }

        .nav-link.active .nav-icon {
            background: #c7e8d5;
            color: #2e7d52;
        }

        .brand-icon {
            background: linear-gradient(135deg, #2e9e63, #3bba7a);
            box-shadow: 0 4px 12px rgba(46, 158, 99, 0.35);
        }

        .topbar-chip {
            background: #fff;
            border: 1.5px solid #d8e4dc;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        }

        .title-accent {
            background: linear-gradient(180deg, #2e9e63, #6ee7b7);
            box-shadow: 0 4px 10px rgba(46, 158, 99, 0.3);
        }

        .shell-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .shell-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        @media (min-width: 768px) {
            .admin-grid {
                display: grid;
                grid-template-columns: var(--sidebar-current-width) minmax(0, 1fr);
                transition: grid-template-columns 280ms cubic-bezier(.16, 1, .3, 1);
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
        ->map(fn ($module, $key) => array_merge($module, [
            'key' => $key,
            'active' => $currentModule === $key,
        ]));
    $currentModuleMeta = $moduleRegistry[$currentModule] ?? null;
    $headerLabel = $currentModuleMeta['name'] ?? 'Platform Admin';
    $headerDescription = match ($currentModule) {
        'campaign' => 'จัดการแคมเปญ การจอง การเช็กอิน และงานบริการผู้ป่วยของคลินิกในมุมมองเดียว',
        'borrow' => 'ดูคำขอยืม สต็อก การคืน และค่าปรับใน flow เดียวสำหรับทีมหน้างาน',
        default => 'เลือก workspace ให้ตรงกับงานที่กำลังทำอยู่ เพื่อให้เมนูและสิทธิ์แสดงเฉพาะสิ่งที่จำเป็นจริง',
    };
@endphp
<body class="overflow-hidden">
    <div
        x-data="{
            sidebarCollapsed: localStorage.getItem('admin-sidebar-collapsed') === 'true',
            mobileSidebarOpen: false,
            toggleSidebar() {
                this.sidebarCollapsed = ! this.sidebarCollapsed;
                localStorage.setItem('admin-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
            }
        }"
        :style="`--sidebar-current-width: ${sidebarCollapsed ? 'var(--sidebar-collapsed-width)' : 'var(--sidebar-expanded-width)'}`"
        class="admin-shell h-screen w-screen overflow-hidden"
    >
        <div
            x-show="mobileSidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/35 backdrop-blur-sm md:hidden"
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
            class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col md:hidden"
            x-cloak
        >
            <div class="shell-scroll flex-1 overflow-y-auto px-3 pb-4 pt-4">
                <div class="mb-4 flex items-center gap-3 border-b border-[#d0ead9] px-3 pb-4">
                    <div class="brand-icon flex h-11 w-11 items-center justify-center rounded-[13px] text-white">
                        <i class="fa-solid {{ $currentModule === 'borrow' ? 'fa-box-open' : 'fa-bullhorn' }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[16px] font-black text-slate-900">RSU Medical Hub</div>
                        <div class="mt-0.5 text-[10px] font-bold uppercase tracking-[.14em] text-[#2e9e63]">Operations Platform</div>
                    </div>
                    <button type="button" @click="mobileSidebarOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#d0ead9] bg-[#f0faf4] text-[#2e9e63]">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mb-5 grid gap-2 px-1">
                    @foreach ($moduleCards as $module)
                        <a href="{{ route($module['route']) }}" class="workspace-chip {{ $module['active'] ? 'active' : '' }} rounded-xl px-4 py-3 text-sm font-black">
                            {{ $module['label'] }}
                        </a>
                    @endforeach
                </div>

                <nav class="space-y-5 px-1">
                    @foreach ($navigationSections as $section)
                        <section>
                            <p class="nav-section-title mb-2 px-3 text-[10px] font-extrabold uppercase">{{ $section['title'] }}</p>
                            <div class="space-y-1">
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
                                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }} px-3 py-3 text-sm font-semibold">
                                            <span class="nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-[#d0ead9] p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-rose-500 transition-all hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-grid h-screen w-screen overflow-hidden">
            <aside :class="sidebarCollapsed ? 'w-[5.5rem]' : 'w-64'" class="admin-sidebar hidden h-screen flex-col md:flex">
                <div class="shell-scroll flex-1 overflow-y-auto px-3 pb-4 pt-4">
                    <div :class="sidebarCollapsed ? 'justify-center' : ''" class="mb-4 flex items-center gap-3 border-b border-[#d0ead9] px-3 pb-4">
                        <div class="brand-icon flex h-[42px] w-[42px] flex-shrink-0 items-center justify-center rounded-[13px] text-white">
                            <i class="fa-solid {{ $currentModule === 'borrow' ? 'fa-box-open' : 'fa-bullhorn' }}"></i>
                        </div>
                        <div x-show="! sidebarCollapsed" x-transition.opacity.duration.180ms class="min-w-0 flex-1">
                            <div class="truncate text-[16px] font-black text-slate-900">RSU Medical Hub</div>
                            <div class="mt-0.5 text-[10px] font-bold uppercase tracking-[.14em] text-[#2e9e63]">{{ $headerLabel }}</div>
                        </div>
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="ml-auto hidden h-9 w-9 items-center justify-center rounded-lg border border-[#d0ead9] bg-[#f0faf4] text-[#2e9e63] transition-all hover:bg-[#e8f8f0] lg:inline-flex"
                            :title="sidebarCollapsed ? 'ขยายเมนู' : 'ย่อเมนู'"
                        >
                            <i class="fa-solid" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                        </button>
                    </div>

                    <div x-show="! sidebarCollapsed" x-transition.opacity.duration.180ms class="mb-5 grid gap-2 px-1">
                        @foreach ($moduleCards as $module)
                            <a href="{{ route($module['route']) }}" class="workspace-chip {{ $module['active'] ? 'active' : '' }} rounded-xl px-4 py-3 text-sm font-black">
                                {{ $module['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <nav class="space-y-5 px-1">
                        @foreach ($navigationSections as $section)
                            <section>
                                <p x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms class="nav-section-title mb-2 px-3 text-[10px] font-extrabold uppercase">{{ $section['title'] }}</p>
                                <div class="space-y-1">
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
                                                class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }} px-3 py-3 text-sm font-semibold"
                                                :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                                                title="{{ $item['label'] }}"
                                            >
                                                <span class="nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                                                <span x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-[#d0ead9] p-3">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" :class="sidebarCollapsed ? 'px-0' : ''" class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-rose-500 transition-all hover:bg-rose-50">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span x-show="! sidebarCollapsed" x-transition.opacity.duration.150ms>ออกจากระบบ</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="main-stage flex min-w-0 flex-1 flex-col overflow-hidden">
                <header class="topbar px-5 py-3 md:px-6 xl:px-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    @click="mobileSidebarOpen = true"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#c7e8d5] bg-[#f0faf4] text-[#2e9e63] md:hidden"
                                    title="เปิดเมนู"
                                >
                                    <i class="fa-solid fa-bars text-sm"></i>
                                </button>
                                <span class="topbar-chip inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.24em] text-slate-500">{{ $headerLabel }}</span>
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-slate-400">{{ $currentModule === 'platform' ? 'Platform Home' : 'Workspace Context' }}</span>
                            </div>
                            <div class="mt-4 flex items-start gap-4">
                                <div class="title-accent h-8 w-1.5 flex-shrink-0 rounded-full"></div>
                                <div>
                                    <h1 class="text-xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $title ?? 'Dashboard' }}</h1>
                                    <p class="mt-2 text-[11px] font-black uppercase tracking-[0.25em] text-[#2e7d52]">{{ $headerDescription }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="topbar-chip flex items-center gap-4 rounded-[18px] px-4 py-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-black leading-none text-slate-900">{{ $adminUser->name ?? 'Administrator' }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $adminUser?->hasFullPlatformAccess() ? 'Full Platform Access' : 'Workspace Admin' }}</p>
                            </div>
                            <div class="h-11 w-11 overflow-hidden rounded-xl border border-[#dbe7df] bg-[#f0faf4] shadow-sm">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($adminUser->name ?? 'A') }}&background=2e9e63&color=fff" class="h-full w-full object-cover" alt="Admin avatar">
                            </div>
                        </div>
                    </div>
                </header>

                <div class="shell-scroll flex-1 overflow-y-auto px-4 py-5 md:px-6 md:py-6 xl:px-8 xl:py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
        window.addEventListener('swal:success', event => {
            Swal.fire({
                title: event.detail.title ?? 'สำเร็จ',
                text: event.detail.message ?? '',
                icon: 'success',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                title: event.detail.title ?? 'เกิดข้อผิดพลาด',
                text: event.detail.message ?? '',
                icon: 'error',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });

        window.addEventListener('swal:info', event => {
            Swal.fire({
                title: event.detail.title ?? 'แจ้งเตือน',
                text: event.detail.message ?? '',
                icon: 'info',
                confirmButtonColor: '#2e9e63',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black' }
            });
        });
    </script>
</body>
</html>
