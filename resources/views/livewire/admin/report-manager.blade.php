<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header & Campaign Selector -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">รายงานและสถิติ</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">Campaign Performance & Analytics</p>
        </div>
        
        <div class="flex flex-col md:flex-row items-end gap-4 flex-1 max-w-2xl">
            <div class="w-full relative group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">เลือกแคมเปญกิจกรรม</label>
                <select wire:model.live="selectedCampaignId" class="w-full pl-6 pr-12 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all appearance-none">
                    <option value="">-- กรุณาเลือกแคมเปญ --</option>
                    @foreach($campaigns as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-5 bottom-[14px] text-slate-400 text-xs pointer-events-none"></i>
            </div>

            @if($selectedCampaignId)
                <button wire:click="export" class="bg-emerald-500 text-white px-8 py-3.5 rounded-2xl font-black shadow-lg shadow-emerald-100 hover:shadow-emerald-200 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3 whitespace-nowrap h-[52px]">
                    <i class="fa-solid fa-file-excel text-lg"></i>
                    <span>Export Excel</span>
                </button>
            @endif
        </div>
    </div>

    @if($selectedCampaignId)
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] mb-4">จองทั้งหมด</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-800 tracking-tight">{{ number_format($stats['total']) }}</span>
                    <span class="text-xs font-bold text-slate-400 uppercase">คน</span>
                </div>
                <div class="mt-4 w-full h-1 bg-slate-50 rounded-full overflow-hidden">
                    <div class="h-full bg-slate-200 w-full"></div>
                </div>
            </div>

            <div class="bg-emerald-50 p-8 rounded-[2.5rem] border border-emerald-100 shadow-sm flex flex-col">
                <span class="text-[10px] font-black text-emerald-300 uppercase tracking-[0.2em] mb-4">เช็คอินแล้ว</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-emerald-600 tracking-tight">{{ number_format($stats['attended']) }}</span>
                    <span class="text-xs font-bold text-emerald-400 uppercase">คน</span>
                </div>
                @php $attendedPercent = $stats['total'] > 0 ? ($stats['attended'] / $stats['total']) * 100 : 0; @endphp
                <div class="mt-4 w-full h-1 bg-emerald-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: {{ $attendedPercent }}%"></div>
                </div>
            </div>

            <div class="bg-rose-50 p-8 rounded-[2.5rem] border border-rose-100 shadow-sm flex flex-col">
                <span class="text-[10px] font-black text-rose-300 uppercase tracking-[0.2em] mb-4">ขาดกิจกรรม</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-rose-600 tracking-tight">{{ number_format($stats['absent']) }}</span>
                    <span class="text-xs font-bold text-rose-400 uppercase">คน</span>
                </div>
                @php $absentPercent = $stats['total'] > 0 ? ($stats['absent'] / $stats['total']) * 100 : 0; @endphp
                <div class="mt-4 w-full h-1 bg-rose-100 rounded-full overflow-hidden">
                    <div class="h-full bg-rose-500" style="width: {{ $absentPercent }}%"></div>
                </div>
            </div>

            <div class="bg-blue-50 p-8 rounded-[2.5rem] border border-blue-100 shadow-sm flex flex-col">
                <span class="text-[10px] font-black text-blue-300 uppercase tracking-[0.2em] mb-4">รอดำเนินการ</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-blue-600 tracking-tight">{{ number_format($stats['pending']) }}</span>
                    <span class="text-xs font-bold text-blue-400 uppercase">คน</span>
                </div>
                @php $pendingPercent = $stats['total'] > 0 ? ($stats['pending'] / $stats['total']) * 100 : 0; @endphp
                <div class="mt-4 w-full h-1 bg-blue-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: {{ $pendingPercent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Detailed List -->
        <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-sm overflow-hidden overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">ชื่อ-นามสกุล / รหัส</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">เบอร์โทรศัพท์</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">รอบเวลาที่นัด</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">เวลาเช็คอิน</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-black text-xs group-hover:bg-white transition-colors">
                                        {{ mb_substr($booking->user->full_name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-700 leading-tight">{{ $booking->user->full_name ?? 'N/A' }}</span>
                                        <span class="text-[11px] font-bold text-blue-500 mt-1 uppercase tracking-wider">{{ $booking->user->identity_label }}: {{ $booking->user->identity_value ?? 'No ID' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-sm font-bold text-slate-500 tracking-tight">{{ $booking->user->phone ?? '-' }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-700">{{ $booking->slot ? $booking->slot->date->format('d/m/Y') : '-' }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">{{ $booking->slot ? substr($booking->slot->start_time, 0, 5) . ' - ' . substr($booking->slot->end_time, 0, 5) : '-' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @if($booking->status === 'attended')
                                    <span class="text-sm font-black text-emerald-600">เช็คอินแล้ว</span>
                                @else
                                    <span class="text-xs font-bold text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                @php
                                    $statusBadge = match($booking->status) {
                                        'attended' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'confirmed' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'absent' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
                                        default => 'bg-amber-50 text-amber-600 border-amber-100'
                                    };
                                    $statusLabel = match($booking->status) {
                                        'attended' => 'เช็คอินแล้ว',
                                        'confirmed' => 'ยืนยันสิทธิ์',
                                        'absent' => 'ขาดกิจกรรม',
                                        'cancelled' => 'ยกเลิกคิว',
                                        default => 'รออนุมัติ'
                                    };
                                @endphp
                                <span class="px-4 py-1.5 rounded-full border {{ $statusBadge }} text-[10px] font-black uppercase tracking-widest">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-32 text-center opacity-40">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                                    <i class="fa-solid fa-file-circle-exclamation text-2xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-400 tracking-wide uppercase tracking-[0.2em]">ยังไม่มีข้อมูลในแคมเปญนี้</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="bg-white px-8 py-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
                {{ $bookings->links() }}
            </div>
        @endif
    @else
        <div class="py-48 text-center opacity-40 bg-white rounded-[3.5rem] border border-slate-100 border-dashed">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mx-auto mb-6">
                <i class="fa-solid fa-chart-pie text-4xl"></i>
            </div>
            <p class="text-lg font-black text-slate-400 tracking-wide uppercase tracking-[0.2em]">กรุณาเลือกแคมเปญเพื่อดูรายงาน</p>
        </div>
    @endif
</div>
