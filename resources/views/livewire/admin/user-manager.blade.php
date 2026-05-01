<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">รายชื่อนักศึกษา / บุคลากร</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">Student & Personnel Database</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative group">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-blue-500 transition-colors"></i>
                <input wire:model.live="search" type="text" placeholder="รหัสนักศึกษา, ชื่อ, เบอร์โทร..." class="pl-11 pr-6 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all w-full md:w-80">
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">ข้อมูลทั่วไป</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">รหัสนักศึกษา/บุคลากร</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">คณะ / หน่วยงาน</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">ติดต่อ</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 font-black text-lg group-hover:bg-white transition-colors">
                                    {{ mb_substr($user->full_name ?: '?', 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-700 leading-tight">{{ $user->full_name ?: 'ยังไม่ได้ระบุชื่อ' }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ $user->gender ?: 'ไม่ระบุเพศ' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $user->identity_label }}</span>
                                <span class="text-sm font-black text-blue-500 tracking-wider">{{ $user->identity_value ?: '—' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm font-bold text-slate-600">{{ $user->department ?: '—' }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-700">{{ $user->phone_number ?: $user->phone ?: '—' }}</span>
                                <span class="text-[10px] font-medium text-slate-400">{{ $user->email ?: '—' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button wire:click="viewHistory({{ $user->id }})" class="px-6 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-slate-100 hover:shadow-slate-200 hover:-translate-y-0.5 active:scale-95 transition-all">
                                <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                                ดูประวัติ
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-32 text-center opacity-40">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <i class="fa-solid fa-user-slash text-2xl"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-400 tracking-wide uppercase tracking-[0.2em]">ไม่พบรายชื่อที่ค้นหา</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white px-8 py-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
        {{ $users->links() }}
    </div>

    <!-- History Modal (Slide-over style) -->
    @if($showHistoryModal && $selectedUser)
        <div class="fixed inset-0 z-[100] flex justify-end bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-2xl h-full shadow-2xl overflow-hidden animate-in slide-in-from-right duration-500">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-3xl bg-blue-600 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-blue-100">
                            {{ mb_substr($selectedUser->full_name ?: '?', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $selectedUser->full_name }}</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Booking History & Engagement</p>
                        </div>
                    </div>
                    <button wire:click="$set('showHistoryModal', false)" class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-10 overflow-y-auto h-[calc(100vh-160px)] custom-scrollbar">
                    <div class="grid grid-cols-2 gap-4 mb-10">
                        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest block mb-2">{{ $selectedUser->identity_label }}</span>
                            <span class="text-sm font-black text-slate-700">{{ $selectedUser->identity_value ?: '—' }}</span>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest block mb-2">คณะ/หน่วยงาน</span>
                            <span class="text-sm font-black text-slate-700">{{ $selectedUser->department ?: '—' }}</span>
                        </div>
                    </div>

                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-8 border-l-4 border-blue-600 pl-4">รายการการจองย้อนหลัง</h4>

                    <div class="space-y-6 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
                        @forelse($userBookings as $booking)
                            <div class="relative pl-16 group">
                                <div class="absolute left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-white border-2 border-slate-200 group-hover:border-blue-500 transition-colors z-10"></div>
                                
                                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">{{ $booking->slot ? $booking->slot->date->format('d M Y') : '-' }}</span>
                                            <h5 class="text-sm font-black text-slate-800">{{ $booking->campaign->title ?? 'N/A' }}</h5>
                                        </div>
                                        @php
                                            $statusBadge = match($booking->status) {
                                                'attended' => 'bg-emerald-50 text-emerald-600',
                                                'confirmed' => 'bg-blue-50 text-blue-600',
                                                'absent' => 'bg-rose-50 text-rose-600',
                                                default => 'bg-amber-50 text-amber-600'
                                            };
                                            $statusLabel = match($booking->status) {
                                                'attended' => 'เช็คอินแล้ว',
                                                'confirmed' => 'ยืนยันแล้ว',
                                                'absent' => 'ขาดกิจกรรม',
                                                default => 'รออนุมัติ'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full {{ $statusBadge }} text-[9px] font-black uppercase tracking-widest">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-[11px] font-bold text-slate-400">
                                        <span><i class="fa-regular fa-clock mr-1.5"></i>{{ $booking->slot ? substr($booking->slot->start_time, 0, 5) : '-' }}</span>
                                        @if($booking->status === 'attended')
                                            <span class="text-emerald-500"><i class="fa-solid fa-circle-check mr-1.5"></i>เช็คอินแล้ว</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 text-center opacity-30">
                                <i class="fa-solid fa-calendar-xmark text-4xl mb-4"></i>
                                <p class="text-xs font-black uppercase tracking-widest">ไม่มีประวัติการจอง</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
