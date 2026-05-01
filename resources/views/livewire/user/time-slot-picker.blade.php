<div class="mt-8">
    @if($date)
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">3. เลือกช่วงเวลา</h3>
                <span class="text-[10px] font-bold text-slate-400">
                    วันที่: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </span>
            </div>

            @if(session()->has('error'))
                <div class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-xs font-bold text-rose-600">
                    <i class="fa-solid fa-circle-xmark mr-1"></i> {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                @foreach($slots as $slot)
                    @php
                        $isFull = $slot->isFull();
                        $remaining = $slot->remainingCapacity();
                    @endphp
                    <button
                        type="button"
                        wire:click="selectSlot({{ $slot->id }})"
                        @if($isFull) disabled @endif
                        class="flex flex-col items-center gap-1 rounded-2xl border-2 p-4 transition-all {{ $selectedSlotId === $slot->id ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-md shadow-emerald-100' : 'border-slate-100 bg-white text-slate-600' }} {{ $isFull ? 'cursor-not-allowed opacity-50 grayscale' : '' }}"
                    >
                        <span class="text-sm font-black">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                        <span class="text-[9px] font-bold opacity-60">ถึง {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }} น.</span>

                        @if($isFull)
                            <span class="mt-1 text-[8px] font-bold uppercase text-rose-500">เต็มแล้ว</span>
                        @else
                            <span class="mt-1 text-[8px] font-bold uppercase text-emerald-500">ว่าง {{ $remaining }} ที่</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @if($selectedSlotId)
                <div class="pt-6">
                    <button
                        type="button"
                        wire:click="confirmBooking"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-3 rounded-2xl bg-emerald-600 py-4 font-black text-white shadow-xl shadow-emerald-200 transition-transform active:scale-95"
                    >
                        <span wire:loading.remove>ยืนยันการนัดหมาย</span>
                        <span wire:loading>กำลังบันทึกข้อมูล...</span>
                        <i class="fa-solid fa-check-circle" wire:loading.remove></i>
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
