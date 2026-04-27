<div class="space-y-8 animate-in fade-in duration-700">
    <!-- KPI Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div wire:click="$set('statusFilter', 'pending')" class="cursor-pointer bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all {{ $statusFilter === 'pending' ? 'ring-2 ring-amber-500 bg-amber-50/30' : '' }}">
            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">รอนุมัติ</p>
                <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['pending']) }}</h4>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'confirmed')" class="cursor-pointer bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all {{ $statusFilter === 'confirmed' ? 'ring-2 ring-emerald-500 bg-emerald-50/30' : '' }}">
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-circle-check text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">ยืนยันแล้ว</p>
                <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['confirmed']) }}</h4>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'cancelled')" class="cursor-pointer bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all {{ $statusFilter === 'cancelled' ? 'ring-2 ring-rose-500 bg-rose-50/30' : '' }}">
            <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-circle-xmark text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">ยกเลิกแล้ว</p>
                <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['cancelled']) }}</h4>
            </div>
        </div>

        <div class="bg-slate-900 p-6 rounded-[2rem] shadow-xl flex items-center gap-5 group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/5 rounded-full blur-2xl"></div>
            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-inner relative z-10">
                <i class="fa-solid fa-calendar-day text-2xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] text-white/40 font-black uppercase tracking-widest mb-1">นัดหมายวันนี้</p>
                <h4 class="text-2xl font-black text-white">{{ number_format($stats['today']) }}</h4>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-sm flex flex-col lg:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-2 p-1.5 bg-slate-50 rounded-[1.5rem] overflow-x-auto no-scrollbar w-full lg:w-auto">
            <button wire:click="$set('statusFilter', 'all')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ทั้งหมด</button>
            <button wire:click="$set('statusFilter', 'pending')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">รอนุมัติ</button>
            <button wire:click="$set('statusFilter', 'confirmed')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'confirmed' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ยืนยันแล้ว</button>
            <button wire:click="$set('statusFilter', 'cancelled')" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'cancelled' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ยกเลิก</button>
        </div>

        <div class="flex-1 w-full lg:max-w-md relative">
            <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input wire:model.live="search" type="text" placeholder="ค้นหาตามชื่อ, รหัส, หรือกิจกรรม..." class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none focus:ring-4 focus:ring-green-50 focus:bg-white focus:border-[#2e9e63] transition-all">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden relative">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] border-b border-slate-100">
                        <td class="p-8 w-12 text-center">
                            <input type="checkbox" class="w-5 h-5 rounded-lg border-slate-300 text-[#2e9e63] focus:ring-[#2e9e63]">
                        </td>
                        <td class="p-8">วันและเวลานัดหมาย</td>
                        <td class="p-8">ข้อมูลผู้จอง</td>
                        <td class="p-8">รายละเอียดแคมเปญ</td>
                        <td class="p-8 text-center">สถานะ</td>
                        <td class="p-8 text-right">การจัดการ</td>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $b)
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-8 py-6 text-center">
                                <input type="checkbox" wire:model.live="selectedBookings" value="{{ $b->id }}" class="w-5 h-5 rounded-lg border-slate-300 text-[#2e9e63] focus:ring-[#2e9e63] cursor-pointer">
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-800 text-sm mb-1">{{ $b->slot ? $b->slot->date->format('d M Y') : 'N/A' }}</div>
                                <div class="text-[10px] text-emerald-600 font-black uppercase tracking-widest">
                                    {{ $b->slot ? substr($b->slot->start_time, 0, 5) . ' - ' . substr($b->slot->end_time, 0, 5) : '-' }}
                                </div>
                            </td>
                            <td class="px-8 py-6 cursor-pointer" wire:click="openDetails({{ $b->id }})">
                                <div class="font-black text-slate-800 group-hover:text-[#2e9e63] transition-colors tracking-tight">{{ $b->user->full_name }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $b->user->student_personnel_id }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-bold text-slate-600 max-w-[200px] truncate">{{ $b->campaign->title }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">CODE: {{ $b->booking_code }}</div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $b->status === 'pending' ? 'bg-amber-50 text-amber-600 border border-amber-100 animate-pulse' : ($b->status === 'confirmed' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400') }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if($b->status === 'pending')
                                        <button wire:click="approve({{ $b->id }})" class="w-10 h-10 bg-[#2e9e63] text-white rounded-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-lg shadow-green-100">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button wire:click="cancel({{ $b->id }})" class="w-10 h-10 bg-white border border-slate-100 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 hover:scale-110 active:scale-95 transition-all shadow-sm">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDetails({{ $b->id }})" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-slate-100 hover:text-slate-600 transition-all">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-32 text-center opacity-40">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4 shadow-inner">
                                    <i class="fa-solid fa-calendar-xmark text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-400 tracking-wide uppercase tracking-[0.2em]">ไม่พบรายการจองในขณะนี้</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($bookings->hasPages())
            <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/30">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Action Bar (Bulk Operations) -->
    @if(count($selectedBookings) > 0)
        <div class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[110] bg-slate-900 px-8 py-5 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] flex items-center gap-10 border border-white/10 animate-in slide-in-from-bottom-10 duration-500">
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400">รายการที่เลือก</span>
                <span class="text-sm font-black text-white">{{ count($selectedBookings) }} รายการ</span>
            </div>
            <div class="w-px h-10 bg-white/10"></div>
            <div class="flex items-center gap-4">
                <button wire:click="bulkApprove" class="bg-[#2e9e63] text-white px-8 py-3.5 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-green-900/20">อนุมัติทั้งหมด</button>
                <button wire:click="bulkCancel" class="bg-rose-600 text-white px-8 py-3.5 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-rose-900/20">ยกเลิกทั้งหมด</button>
                <button wire:click="$set('selectedBookings', [])" class="text-slate-400 hover:text-white transition-colors"><i class="fa-solid fa-times"></i></button>
            </div>
        </div>
    @endif

    <!-- Side Drawer (Details) -->
    @if($showDrawer && $selectedBookingDetails)
        <div class="fixed inset-0 z-[120] flex items-center justify-end overflow-hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300" wire:click="closeDrawer"></div>
            
            <aside class="relative bg-white w-full max-w-xl h-full shadow-2xl animate-in slide-in-from-right duration-500 flex flex-col">
                <div class="p-10 border-b border-slate-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Booking Details</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">รหัสการจอง: {{ $selectedBookingDetails->booking_code }}</p>
                    </div>
                    <button wire:click="closeDrawer" class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all shadow-sm">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-10 space-y-10 custom-scrollbar">
                    <!-- User Profile -->
                    <div class="space-y-4">
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-widest">ข้อมูลผู้จอง</span>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 text-3xl border border-slate-100 shadow-inner">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h4 class="text-3xl font-black text-slate-800 leading-tight mb-1">{{ $selectedBookingDetails->user->full_name }}</h4>
                                <p class="text-slate-400 font-bold tracking-widest text-sm">ID: {{ $selectedBookingDetails->user->student_personnel_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Meta -->
                    <div class="grid grid-cols-2 gap-8">
                        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">เบอร์โทรศัพท์</label>
                            <p class="text-lg font-black text-slate-800">{{ $selectedBookingDetails->user->phone_number ?? '-' }}</p>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">คณะ/หน่วยงาน</label>
                            <p class="text-lg font-black text-slate-800">{{ $selectedBookingDetails->user->department ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Schedule Info -->
                    <div class="p-10 bg-[#2e9e63] rounded-[3rem] text-white shadow-xl shadow-green-100 relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/50 block mb-6 relative z-10">กำหนดเวลานัดหมาย</label>
                        <div class="flex items-center gap-6 relative z-10">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-3xl shadow-inner border border-white/10">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-black leading-none mb-2">{{ $selectedBookingDetails->slot ? $selectedBookingDetails->slot->date->format('d F Y') : 'N/A' }}</p>
                                <p class="text-sm font-bold text-white/80">{{ $selectedBookingDetails->slot ? substr($selectedBookingDetails->slot->start_time, 0, 5) . ' - ' . substr($selectedBookingDetails->slot->end_time, 0, 5) : '-' }} น.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Info -->
                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block ml-2">แคมเปญที่เข้าร่วม</label>
                        <div class="p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100 flex items-center gap-5">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 shadow-sm border border-slate-100">
                                <i class="fa-solid fa-bullhorn text-sm"></i>
                            </div>
                            <p class="text-base font-black text-slate-700 leading-tight">{{ $selectedBookingDetails->campaign->title }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-10 bg-slate-50 border-t border-slate-100 flex gap-4">
                    @if($selectedBookingDetails->status === 'pending')
                        <button wire:click="approve({{ $selectedBookingDetails->id }})" class="flex-1 h-16 bg-[#2e9e63] text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-green-100 active:scale-95 transition-all">ยืนยันการจองคิว</button>
                        <button wire:click="cancel({{ $selectedBookingDetails->id }})" class="flex-1 h-16 bg-white text-rose-500 rounded-2xl font-black uppercase tracking-widest text-xs border border-slate-200 active:scale-95 transition-all">ยกเลิก</button>
                    @else
                        <button wire:click="closeDrawer" class="w-full h-16 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-slate-200 active:scale-95 transition-all">ปิดหน้าต่าง</button>
                    @endif
                </div>
            </aside>
        </div>
    @endif
</div>
