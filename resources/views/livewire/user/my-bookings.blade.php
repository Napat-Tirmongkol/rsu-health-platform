<div
    x-data="{ open: false }"
    @open-modal.window="open = true"
    @close-modal.window="open = false"
>
    <style>
        .booking-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .booking-card:active { transform: scale(0.97); }
        .sheet-backdrop { background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); }
    </style>

    <div class="mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="flex gap-3 rounded-3xl border border-gray-100 bg-gray-50/80 p-4 shadow-sm backdrop-blur-md">
            <div class="flex-1 rounded-2xl border border-green-100/50 bg-white p-3 text-center shadow-sm">
                <p class="text-xl font-black text-[#2e9e63]">{{ $stats['upcoming'] }}</p>
                <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">รอรับบริการ</p>
            </div>
            <div class="flex-1 rounded-2xl border border-gray-100 bg-white p-3 text-center shadow-sm">
                <p class="text-xl font-black text-gray-900">{{ $stats['history'] }}</p>
                <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">ประวัติทั้งหมด</p>
            </div>
            <div class="flex-1 rounded-2xl border border-emerald-100/50 bg-white p-3 text-center shadow-sm">
                <p class="text-xl font-black text-emerald-600">{{ $stats['checkin'] }}</p>
                <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">เช็คอินแล้ว</p>
            </div>
        </div>
    </div>

    <div class="mb-6 flex rounded-2xl border border-gray-100 bg-white p-1.5 shadow-lg">
        <button
            wire:click="switchTab('upcoming')"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl py-3 text-sm font-bold transition-all {{ $tab === 'upcoming' ? 'bg-[#2e9e63] text-white shadow-md' : 'text-gray-500' }}"
        >
            <i class="fa-solid fa-calendar-clock text-xs"></i>
            นัดหมายใหม่
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $tab === 'upcoming' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $stats['upcoming'] }}</span>
        </button>
        <button
            wire:click="switchTab('history')"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl py-3 text-sm font-bold transition-all {{ $tab === 'history' ? 'bg-[#2e9e63] text-white shadow-md' : 'text-gray-500' }}"
        >
            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
            ประวัติย้อนหลัง
            <span class="rounded-full px-2 py-0.5 text-[10px] {{ $tab === 'history' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $stats['history'] }}</span>
        </button>
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $statusConfig = [
                    'pending' => ['grad' => 'from-amber-400 to-yellow-400', 'bg' => 'bg-amber-100 text-amber-700', 'icon' => 'fa-hourglass-half', 'label' => 'รอตรวจสอบ'],
                    'confirmed' => ['grad' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'fa-calendar-check', 'label' => 'ยืนยันแล้ว'],
                    'completed' => ['grad' => 'from-sky-400 to-blue-500', 'bg' => 'bg-sky-100 text-sky-700', 'icon' => 'fa-check-double', 'label' => 'เข้ารับบริการแล้ว'],
                    'cancelled' => ['grad' => 'from-gray-400 to-slate-500', 'bg' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-ban', 'label' => 'ยกเลิกแล้ว'],
                    'attended' => ['grad' => 'from-sky-400 to-blue-500', 'bg' => 'bg-sky-100 text-sky-700', 'icon' => 'fa-check-double', 'label' => 'เข้ารับบริการแล้ว'],
                ];
                $cfg = $statusConfig[$booking->status] ?? $statusConfig['pending'];
            @endphp

            <button
                type="button"
                wire:click="showDetails({{ $booking->id }})"
                class="booking-card w-full overflow-hidden rounded-2xl border border-gray-100 bg-white text-left shadow-sm"
            >
                <div class="h-1 w-full bg-gradient-to-r {{ $cfg['grad'] }}"></div>

                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $cfg['grad'] }} shadow-sm">
                                <i class="fa-solid {{ $booking->campaign->type === 'vaccine' ? 'fa-syringe' : 'fa-stethoscope' }} text-base text-white"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="mb-0.5 truncate text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ $booking->campaign->title }}
                                </p>
                                <p class="text-[15px] font-bold leading-tight text-gray-900">
                                    {{ \Carbon\Carbon::parse($booking->slot->date)->format('d M Y') }}
                                </p>
                                <p class="mt-0.5 text-[12px] font-semibold text-[#2e9e63]">
                                    <i class="fa-regular fa-clock mr-1 text-xs"></i>
                                    {{ \Carbon\Carbon::parse($booking->slot->start_time)->format('H:i') }} น.
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $cfg['bg'] }}">
                            <i class="fa-solid {{ $cfg['icon'] }} text-[8px]"></i>
                            {{ $cfg['label'] }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
                        <span class="text-[11px] font-bold text-slate-400">แตะเพื่อดูรายละเอียด</span>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                    </div>

                    @if($booking->status === 'confirmed' && $booking->slot->date >= now()->format('Y-m-d'))
                        <div class="mt-4 border-t border-gray-50 pt-4">
                            <button
                                type="button"
                                wire:click.stop="cancelBooking({{ $booking->id }})"
                                wire:confirm="คุณแน่ใจหรือไม่ว่าต้องการยกเลิกนัดหมายนี้?"
                                class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-rose-50 py-2.5 text-[11px] font-black text-rose-500 transition-all active:scale-95"
                            >
                                <i class="fa-regular fa-circle-xmark"></i> ยกเลิกนัดหมายนี้
                            </button>
                        </div>
                    @endif
                </div>
            </button>
        @empty
            <div class="flex flex-col items-center justify-center space-y-4 py-20 text-center opacity-50">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                    <i class="fa-solid fa-clipboard-list text-3xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-400">ไม่พบรายการนัดหมายในหมวดนี้</p>
            </div>
        @endforelse
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="sheet-backdrop fixed inset-0 z-[100] flex items-end justify-center"
        style="display: none;"
        @click="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative max-h-[90vh] w-full max-w-md overflow-y-auto rounded-t-[2.5rem] bg-white pb-10 shadow-2xl"
            @click.stop
        >
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-50 bg-white px-6 pb-2 pt-4">
                <div class="absolute left-1/2 top-3 h-1.5 w-10 -translate-x-1/2 rounded-full bg-gray-100"></div>
                <h2 class="mt-2 text-lg font-black text-gray-900">รายละเอียดนัดหมาย</h2>
                <button type="button" @click="open = false" class="mt-2 flex h-9 w-9 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if($selectedBooking)
                @php
                    $statusConfig = [
                        'pending' => ['grad' => 'from-amber-400 to-yellow-400', 'bg' => 'bg-amber-100 text-amber-700', 'icon' => 'fa-hourglass-half', 'label' => 'รอตรวจสอบ'],
                        'confirmed' => ['grad' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'fa-calendar-check', 'label' => 'ยืนยันแล้ว'],
                        'completed' => ['grad' => 'from-sky-400 to-blue-500', 'bg' => 'bg-sky-100 text-sky-700', 'icon' => 'fa-check-double', 'label' => 'เข้ารับบริการแล้ว'],
                        'cancelled' => ['grad' => 'from-gray-400 to-slate-500', 'bg' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-ban', 'label' => 'ยกเลิกแล้ว'],
                        'attended' => ['grad' => 'from-sky-400 to-blue-500', 'bg' => 'bg-sky-100 text-sky-700', 'icon' => 'fa-check-double', 'label' => 'เข้ารับบริการแล้ว'],
                    ];
                    $cfg = $statusConfig[$selectedBooking->status] ?? $statusConfig['pending'];
                @endphp

                <div class="p-6">
                    <div class="mb-8 rounded-3xl bg-slate-50 py-6 text-center">
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full {{ explode(' ', $cfg['bg'])[0] }}">
                            <i class="fa-solid {{ $cfg['icon'] }} text-2xl {{ explode(' ', $cfg['bg'])[1] }}"></i>
                        </div>
                        <p class="text-base font-black {{ explode(' ', $cfg['bg'])[1] }}">{{ $cfg['label'] }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            {{ $selectedBooking->status === 'confirmed' ? 'กรุณาแสดง QR Code ต่อเจ้าหน้าที่' : 'สถานะนัดหมายของคุณ' }}
                        </p>
                    </div>

                    <div class="mb-8 space-y-5 rounded-[2rem] border border-gray-100 bg-gray-50 p-6 text-left">
                        <div>
                            <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                <i class="fa-solid fa-layer-group mr-1.5 text-emerald-500"></i>กิจกรรม / บริการ
                            </p>
                            <p class="text-base font-black leading-tight text-slate-800">{{ $selectedBooking->campaign->title }}</p>
                        </div>

                        <div class="h-px bg-gray-200/50"></div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i class="fa-regular fa-calendar mr-1.5 text-emerald-500"></i>วันที่นัดหมาย
                                </p>
                                <p class="text-[13px] font-black text-slate-800">{{ \Carbon\Carbon::parse($selectedBooking->slot->date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i class="fa-regular fa-clock mr-1.5 text-emerald-500"></i>เวลานัดหมาย
                                </p>
                                <p class="text-[13px] font-black text-slate-800">
                                    {{ \Carbon\Carbon::parse($selectedBooking->slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($selectedBooking->slot->end_time)->format('H:i') }} น.
                                </p>
                            </div>
                        </div>

                        <div class="h-px bg-gray-200/50"></div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i class="fa-solid fa-hashtag mr-1.5 text-emerald-500"></i>รหัสการจอง
                                </p>
                                <p class="text-[13px] font-black text-slate-800">{{ $selectedBooking->booking_code }}</p>
                            </div>
                            <div>
                                <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i class="fa-solid fa-clipboard-check mr-1.5 text-emerald-500"></i>สถานะ
                                </p>
                                <p class="text-[13px] font-black {{ explode(' ', $cfg['bg'])[1] }}">{{ $cfg['label'] }}</p>
                            </div>
                        </div>

                        <div class="h-px bg-gray-200/50"></div>

                        <div>
                            <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                <i class="fa-solid fa-location-dot mr-1.5 text-emerald-500"></i>รายละเอียดบริการ
                            </p>
                            <p class="text-[12px] font-bold leading-relaxed text-slate-600">{{ $selectedBooking->campaign->description ?: 'RSU Medical Clinic' }}</p>
                        </div>

                        @if($selectedBooking->notes)
                            <div class="h-px bg-gray-200/50"></div>
                            <div>
                                <p class="mb-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i class="fa-solid fa-note-sticky mr-1.5 text-emerald-500"></i>หมายเหตุ
                                </p>
                                <p class="text-[12px] font-bold leading-relaxed text-slate-600">{{ $selectedBooking->notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($selectedBooking->status === 'confirmed')
                        <div class="text-center">
                            <div class="mb-4 inline-block rounded-[2.5rem] border-2 border-emerald-50 bg-white p-5 shadow-xl shadow-emerald-900/5">
                                <div class="flex h-44 w-44 items-center justify-center rounded-2xl bg-slate-50 text-4xl text-slate-800">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                            </div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">REF: {{ $selectedBooking->booking_code }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
