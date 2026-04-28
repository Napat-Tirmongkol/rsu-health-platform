<div class="space-y-6">
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">1. เลือกแคมเปญ</h3>
            @if($selectedCampaign)
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                    พร้อมจอง
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-3">
            @forelse($campaigns as $campaign)
                <button
                    type="button"
                    wire:click="selectCampaign({{ $campaign->id }})"
                    class="premium-card flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-4 text-left shadow-sm transition-all {{ $selectedCampaign && $selectedCampaign->id === $campaign->id ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/20' : '' }}"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm">
                        <i class="fa-solid fa-syringe text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm font-bold text-slate-800">{{ $campaign->title }}</h4>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">{{ Str::limit($campaign->description, 80) }}</p>
                    </div>
                    @if($selectedCampaign && $selectedCampaign->id === $campaign->id)
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    @else
                        <i class="fa-solid fa-chevron-right text-slate-300"></i>
                    @endif
                </button>
            @empty
                <p class="rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">ยังไม่มีแคมเปญที่เปิดให้จองในตอนนี้</p>
            @endforelse
        </div>
    </div>

    @if($selectedCampaign)
        <div class="space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">2. เลือกวันที่</h3>

            @if(empty($availableDates))
                <p class="rounded-2xl border border-rose-100 bg-rose-50 p-4 text-xs text-rose-500">
                    ยังไม่มีวันที่ว่างสำหรับแคมเปญนี้
                </p>
            @else
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach($availableDates as $date)
                        @php($d = \Carbon\Carbon::parse($date))
                        <button
                            type="button"
                            wire:click="selectDate('{{ $date }}')"
                            class="flex h-20 w-16 flex-shrink-0 flex-col items-center justify-center rounded-2xl transition-all {{ $selectedDate === $date ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'border border-slate-100 bg-white text-slate-600' }}"
                        >
                            <span class="text-[10px] font-bold uppercase opacity-80">{{ $d->format('D') }}</span>
                            <span class="my-1 text-xl font-black">{{ $d->format('d') }}</span>
                            <span class="text-[10px] font-bold">{{ $d->format('M') }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
