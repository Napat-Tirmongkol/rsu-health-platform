<div class="space-y-6">

    {{-- Flash --}}
    @if (session()->has('log_msg'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('log_msg') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <div class="relative col-span-1 xl:col-span-2">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input
                wire:model.live="search"
                type="text"
                placeholder="ค้นหา action, description, IP..."
                class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50"
            >
        </div>
        <select wire:model.live="filterClinic" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
            <option value="">ทุกคลินิก</option>
            @foreach ($clinics as $clinic)
                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterAction" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
            <option value="">ทุก Action</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}">{{ $action }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
            <input wire:model.live="dateTo"   type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
        </div>
    </div>

    {{-- Pagination info --}}
    <p class="text-sm font-bold text-slate-500">
        หน้า {{ $logs->lastPage() > 0 ? $logs->currentPage() : 0 }} / {{ $logs->lastPage() }} · รวม {{ number_format($logs->total()) }} รายการ
    </p>

    {{-- Table --}}
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-4">Clinic</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4">Actor</th>
                        <th class="px-6 py-4">IP</th>
                        <th class="px-6 py-4">เวลา</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <span class="rounded-xl border border-sky-100 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-sky-700">
                                    {{ $log->clinic?->name ?? 'Global' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="max-w-[22rem] px-6 py-4 text-sm font-bold text-slate-600">
                                {{ \Illuminate\Support\Str::limit($log->description, 80) ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-500">
                                @if($log->actor_type)
                                    <span class="text-[10px] font-black uppercase tracking-wide text-slate-400">{{ class_basename($log->actor_type) }}</span>
                                    <div>#{{ $log->actor_id }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-sm font-bold text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-500">{{ $log->created_at->format('d/m/y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ไม่มีข้อมูล activity log</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $logs->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

</div>
