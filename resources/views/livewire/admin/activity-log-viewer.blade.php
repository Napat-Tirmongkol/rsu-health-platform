<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header & Filters -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">บันทึกกิจกรรมระบบ</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">System Audit Trail & Security Logs</p>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <!-- Action Filter -->
            <div class="relative group">
                <select wire:model.live="filterAction" class="pl-6 pr-12 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all appearance-none min-w-[180px]">
                    <option value="all">ทุกประเภทกิจกรรม</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ strtoupper($action) }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-filter absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs pointer-events-none"></i>
            </div>

            <div class="relative group">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-blue-500 transition-colors"></i>
                <input wire:model.live="search" type="text" placeholder="ค้นหาบันทึก..." class="pl-11 pr-6 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all w-full md:w-64">
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">วัน-เวลา</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">ผู้ดำเนินการ</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">กิจกรรม</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">รายละเอียด</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-700">{{ $log->created_at->format('d M Y') }}</span>
                                <span class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $log->created_at->format('H:i:s') }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-100 group-hover:bg-white transition-colors">
                                    <i class="fa-solid fa-user-shield text-xs"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-700">{{ $log->actor ? ($log->actor->full_name ?? $log->actor->name) : 'System' }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-wider">
                                        @if($log->actor_type)
                                            {{ class_basename($log->actor_type) }}
                                        @else
                                            Automated
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @php
                                $color = match(true) {
                                    str_contains(strtolower($log->action), 'delete') => 'bg-rose-50 text-rose-500 border-rose-100',
                                    str_contains(strtolower($log->action), 'create') => 'bg-emerald-50 text-emerald-500 border-emerald-100',
                                    str_contains(strtolower($log->action), 'update') => 'bg-blue-50 text-blue-500 border-blue-100',
                                    str_contains(strtolower($log->action), 'login') => 'bg-amber-50 text-amber-500 border-amber-100',
                                    default => 'bg-slate-50 text-slate-500 border-slate-100'
                                };
                            @endphp
                            <span class="px-3 py-1.5 rounded-xl border {{ $color }} text-[10px] font-black uppercase tracking-wider">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-md">{{ $log->description }}</p>
                        </td>
                        <td class="px-8 py-6">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                <i class="fa-solid fa-network-wired text-[10px] text-slate-300"></i>
                                <code class="text-[11px] font-bold text-slate-400 tracking-tight">{{ $log->ip_address ?: '0.0.0.0' }}</code>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-32 text-center opacity-40">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-400 tracking-wide uppercase tracking-[0.2em]">ไม่พบประวัติกิจกรรมในช่วงนี้</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white px-8 py-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
        {{ $logs->links() }}
    </div>
</div>
