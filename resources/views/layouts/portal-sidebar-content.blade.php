@php
    $portalUser  = Auth::guard('portal')->user();
    $navSections = config('portal_nav.sections', []);
    $collapsed   = $collapsed ?? false;
@endphp

{{-- Logo --}}
@if (! $collapsed)
    <div class="mb-8 flex items-center gap-4">
        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl shadow-lg" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
            <i class="fa-solid fa-shield-halved text-lg text-white"></i>
        </div>
        <div>
            <h1 class="text-lg font-black leading-tight text-white">RSU Medical Hub</h1>
            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.28em] text-violet-400">Superadmin Portal</p>
        </div>
    </div>

    {{-- Portal badge --}}
    <div class="shell-card mb-8 rounded-[2rem] p-5">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl" style="background: rgba(124, 58, 237, 0.18); color: #a78bfa;">
                <i class="fa-solid fa-user-shield text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">Superadmin</p>
                <p class="mt-0.5 truncate text-sm font-black text-white">{{ $portalUser?->name ?? 'Portal Admin' }}</p>
                <p class="truncate text-[11px] font-bold text-slate-400">{{ $portalUser?->email ?? '' }}</p>
            </div>
        </div>
        <div class="mt-4 rounded-xl border px-3 py-2 text-center text-[10px] font-black uppercase tracking-widest" style="border-color: rgba(124,58,237,0.28); color: #a78bfa; background: rgba(124,58,237,0.08);">
            Full System Access
        </div>
    </div>
@endif

{{-- Navigation --}}
<nav class="space-y-7">
    @foreach ($navSections as $section)
        <section>
            @if (! $collapsed)
                <p class="mb-3 px-3 text-[10px] font-black uppercase tracking-[0.28em] text-slate-500">
                    {{ $section['title'] }}
                </p>
            @endif
            <div class="space-y-2">
                @foreach ($section['items'] as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }} flex items-center gap-3 px-4 py-3.5 text-sm font-bold {{ $collapsed ? 'justify-center px-0' : '' }}"
                        title="{{ $item['label'] }}"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs($item['route']) ? 'bg-white/10 text-white' : 'bg-white/5 text-slate-300' }}">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </span>
                        @if (! $collapsed)
                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</nav>
