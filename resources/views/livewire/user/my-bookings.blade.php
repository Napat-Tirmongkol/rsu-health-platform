<div x-data="{ open: false }" @open-modal.window="open = true">
    <style>
        .booking-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .booking-card:active { transform: scale(0.97); }
        .sheet-backdrop { background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); }
    </style>

    <!-- Quick stats panel -->
    <div class="mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="bg-gray-50/80 backdrop-blur-md border border-gray-100 rounded-3xl p-4 shadow-sm flex gap-3">
            <div class="flex-1 bg-white rounded-2xl p-3 text-center border border-green-100/50 shadow-sm">
                <p class="text-xl font-black text-[#2e9e63]">{{ $stats['upcoming'] }}</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">รอรับบริการ</p>
            </div>
            <div class="flex-1 bg-white rounded-2xl p-3 text-center border border-gray-100 shadow-sm">
                <p class="text-xl font-black text-gray-900">{{ $stats['history'] }}</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">ประวัติทั้งหมด</p>
            </div>
            <div class="flex-1 bg-white rounded-2xl p-3 text-center border border-emerald-100/50 shadow-sm">
                <p class="text-xl font-black text-emerald-600">{{ $stats['checkin'] }}</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">เช็คอินแล้ว</p>
            </div>
        </div>
    </div>

    <!-- Tab Switcher -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-1.5 mb-6 flex">
        <button wire:click="switchTab('upcoming')"
                class="flex-1 py-3 text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 {{ $tab === 'upcoming' ? 'bg-[#2e9e63] text-white shadow-md' : 'text-gray-500' }}">
            <i class="fa-solid fa-calendar-clock text-xs"></i>
            นัดหมายใหม่
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'upcoming' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $stats['upcoming'] }}</span>
        </button>
        <button wire:click="switchTab('history')"
                class="flex-1 py-3 text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 {{ $tab === 'history' ? 'bg-[#2e9e63] text-white shadow-md' : 'text-gray-500' }}">
            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
            ประวัติย้อนหลัง
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'history' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $stats['history'] }}</span>
        </button>
    </div>

    <!-- Booking List -->
    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $statusConfig = [
                    'pending'   => ['grad' => 'from-amber-400 to-yellow-400', 'bg' => 'bg-amber-100 text-amber-700', 'icon' => 'fa-hourglass-half', 'label' => 'รอตรวจสอบ'],
                    'confirmed' => ['grad' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'fa-calendar-check', 'label' => 'ยืนยันแล้ว'],
                    'completed' => ['grad' => 'from-sky-400 to-blue-500', 'bg' => 'bg-sky-100 text-sky-700', 'icon' => 'fa-check-double', 'label' => 'เข้ารับบริการแล้ว'],
                    'cancelled' => ['grad' => 'from-gray-400 to-slate-500', 'bg' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-ban', 'label' => 'ยกเลิกแล้ว'],
                ];
                $cfg = $statusConfig[$booking->status] ?? $statusConfig['pending'];
            @endphp
            
            <div wire:click="showDetails({{ $booking->id }})" 
                 class="booking-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden cursor-pointer text-left">
                
                <div class="h-1 w-full bg-gradient-to-r {{ $cfg['grad'] }}"></div>

                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="shrink-0 w-11 h-11 rounded-xl bg-gradient-to-br {{ $cfg['grad'] }} flex items-center justify-center shadow-sm">
                                <i class="fa-solid {{ $booking->campaign->type === 'vaccine' ? 'fa-syringe' : 'fa-stethoscope' }} text-white text-base"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate mb-0.5">
                                    {{ $booking->campaign->title }}
                                </p>
                                <p class="font-bold text-gray-900 text-[15px] leading-tight">
                                    {{ \Carbon\Carbon::parse($booking->slot->date)->format('d M Y') }}
                                </p>
                                <p class="text-[12px] text-[#2e9e63] font-semibold mt-0.5">
                                    <i class="fa-regular fa-clock text-xs mr-1"></i>
                                    {{ \Carbon\Carbon::parse($booking->slot->start_time)->format('H:i') }} น.
                                </p>
                            </div>
                        </div>

                        <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black border uppercase tracking-wider {{ $cfg['bg'] }}">
                            <i class="fa-solid {{ $cfg['icon'] }} text-[8px]"></i>
                            {{ $cfg['label'] }}
                        </span>
                    </div>

                    @if($booking->status === 'confirmed' && $booking->slot->date >= now()->format('Y-m-d'))
                        <div class="mt-4 pt-4 border-t border-gray-50">
                            <button wire:click.stop="cancelBooking({{ $booking->id }})"
                                    wire:confirm="คุณแน่ใจหรือไม่ว่าต้องการยกเลิกนัดหมายนี้?"
                                    class="w-full py-2.5 text-[11px] font-black text-rose-500 bg-rose-50 rounded-xl flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                                <i class="fa-regular fa-circle-xmark"></i> ยกเลิกนัดหมายนี้
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-20 flex flex-col items-center justify-center text-center space-y-4 opacity-50">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                    <i class="fa-solid fa-clipboard-list text-3xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-400">ไม่พบรายการนัดหมายในหมวดนี้</p>
            </div>
        @endforelse
    </div>

    <!-- ===== BOTTOM SHEET MODAL ===== -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-end justify-center sheet-backdrop"
         style="display: none;"
         @click="open = false">
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="relative bg-white w-full max-w-md rounded-t-[2.5rem] shadow-2xl max-h-[90vh] overflow-y-auto pb-10"
             @click.stop>

            <!-- Drag Handle -->
            <div class="sticky top-0 z-10 bg-white pt-4 pb-2 px-6 flex items-center justify-between border-b border-gray-50">
                <div class="absolute left-1/2 top-3 -translate-x-1/2 w-10 h-1.5 bg-gray-100 rounded-full"></div>
                <h2 class="text-lg font-black text-gray-900 mt-2">รายละเอียดนัดหมาย</h2>
                <button @click="open = false" class="w-9 h-9 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mt-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if($selectedBooking)
                <div class="p-6">
                    <!-- Status Section -->
                    @php
                        $cfg = $statusConfig[$selectedBooking->status] ?? $statusConfig['pending'];
                    @endphp
                    <div class="flex flex-col items-center text-center mb-8 py-6 rounded-3xl {{ str_replace('text-', 'bg-', explode(' ', $cfg['bg'])[1]) }}0">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 {{ str_replace('text-', 'bg-', explode(' ', $cfg['bg'])[1]) }}20">
                            <i class="fa-solid {{ $cfg['icon'] }} text-2xl {{ explode(' ', $cfg['bg'])[1] }}"></i>
                        </div>
                        <p class="font-black text-base {{ explode(' ', $cfg['bg'])[1] }}">{{ $cfg['label'] }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                            {{ $selectedBooking->status === 'confirmed' ? 'กรุณาแสดง QR Code ต่อเจ้าหน้าที่' : 'สถานะนัดหมายของคุณ' }}
                        </p>
                    </div>

                    <!-- Details Grid -->
                    <div class="bg-gray-50 rounded-[2rem] p-6 space-y-5 border border-gray-100 mb-8 text-left">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">
                                <i class="fa-solid fa-layer-group mr-1.5 text-emerald-500"></i>กิจกรรม / บริการ
                            </p>
                            <p class="font-black text-slate-800 text-base leading-tight">{{ $selectedBooking->campaign->title }}</p>
                        </div>
                        <div class="h-px bg-gray-200/50"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">
                                    <i class="fa-regular fa-calendar mr-1.5 text-emerald-500"></i>วันที่นัดหมาย
                                </p>
                                <p class="font-black text-slate-800 text-[13px]">{{ \Carbon\Carbon::parse($selectedBooking->slot->date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">
                                    <i class="fa-regular fa-clock mr-1.5 text-emerald-500"></i>เวลาที่นัดไว้
                                </p>
                                <p class="font-black text-slate-800 text-[13px]">{{ \Carbon\Carbon::parse($selectedBooking->slot->start_time)->format('H:i') }} น.</p>
                            </div>
                        </div>
                        <div class="h-px bg-gray-200/50"></div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">
                                <i class="fa-solid fa-location-dot mr-1.5 text-emerald-500"></i>สถานที่เข้ารับบริการ
                            </p>
                            <p class="text-[12px] text-slate-600 font-bold leading-relaxed">{{ $selectedBooking->campaign->description }}</p>
                        </div>
                    </div>

                    <!-- QR Code -->
                    @if($selectedBooking->status === 'confirmed')
                        <div class="text-center animate-in zoom-in duration-500">
                            <div class="inline-block bg-white p-5 rounded-[2.5rem] border-2 border-emerald-50 shadow-xl shadow-emerald-900/5 mb-4">
                                <div class="w-44 h-44 bg-slate-50 rounded-2xl flex items-center justify-center text-4xl text-slate-800">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">REF: {{ $selectedBooking->id }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
