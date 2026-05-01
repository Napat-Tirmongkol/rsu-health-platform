@props([
    'icon' => 'fa-circle',
    'badge' => null,
    'eyebrow' => null,
    'value' => null,
    'description' => null,
    'variant' => 'default',
])

@php
    $variants = [
        'default' => [
            'card' => 'border border-emerald-100 bg-white text-slate-900',
            'iconWrap' => 'bg-slate-100 text-slate-700',
            'badge' => 'bg-slate-100 text-slate-500 border border-slate-200',
            'eyebrow' => 'text-slate-400',
            'value' => 'text-slate-950',
            'description' => 'text-slate-500',
        ],
        'success' => [
            'card' => 'border border-emerald-100 bg-white text-slate-900',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'eyebrow' => 'text-slate-400',
            'value' => 'text-slate-950',
            'description' => 'text-slate-500',
        ],
        'warning' => [
            'card' => 'border border-amber-100 bg-white text-slate-900',
            'iconWrap' => 'bg-amber-100 text-amber-700',
            'badge' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'eyebrow' => 'text-slate-400',
            'value' => 'text-slate-950',
            'description' => 'text-slate-500',
        ],
        'danger' => [
            'card' => 'border border-rose-100 bg-white text-slate-900',
            'iconWrap' => 'bg-rose-100 text-rose-700',
            'badge' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'eyebrow' => 'text-slate-400',
            'value' => 'text-slate-950',
            'description' => 'text-slate-500',
        ],
        'info' => [
            'card' => 'border border-cyan-100 bg-white text-slate-900',
            'iconWrap' => 'bg-cyan-100 text-cyan-700',
            'badge' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            'eyebrow' => 'text-slate-400',
            'value' => 'text-slate-950',
            'description' => 'text-slate-500',
        ],
        'soft-accent' => [
            'card' => 'border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 text-slate-900',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'bg-white text-emerald-700 border border-emerald-200',
            'eyebrow' => 'text-slate-500',
            'value' => 'text-slate-950',
            'description' => 'text-slate-600',
        ],
        'soft-cyan' => [
            'card' => 'border border-cyan-200 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 text-slate-900',
            'iconWrap' => 'bg-cyan-100 text-cyan-700',
            'badge' => 'bg-white text-cyan-700 border border-cyan-200',
            'eyebrow' => 'text-slate-500',
            'value' => 'text-slate-950',
            'description' => 'text-slate-600',
        ],
    ];

    $theme = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->class(['rounded-[2rem] p-6 shadow-sm transition-all', $theme['card']]) }}>
    <div class="flex items-center justify-between">
        <div class="{{ 'flex h-14 w-14 items-center justify-center rounded-2xl ' . $theme['iconWrap'] }}">
            <i class="fa-solid {{ $icon }} text-xl"></i>
        </div>
        @if($badge)
            <span class="{{ 'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] ' . $theme['badge'] }}">{{ $badge }}</span>
        @endif
    </div>

    @if($eyebrow)
        <p class="{{ 'mt-6 text-[10px] font-black uppercase tracking-[0.28em] ' . $theme['eyebrow'] }}">{{ $eyebrow }}</p>
    @endif

    @if(! is_null($value))
        <h3 class="{{ 'mt-2 text-3xl font-black tracking-tight ' . $theme['value'] }}">{{ $value }}</h3>
    @endif

    @if($description)
        <p class="{{ 'mt-3 text-sm font-bold leading-relaxed ' . $theme['description'] }}">{{ $description }}</p>
    @endif
</div>
