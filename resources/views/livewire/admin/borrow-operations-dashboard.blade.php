<div class="space-y-8">
    @php($adminUser = Auth::guard('admin')->user())

    @if (! $tablesReady)
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            ส่วนสรุป e-Borrow จะแสดงข้อมูลเต็มเมื่อรัน <code class="rounded bg-white px-2 py-1 text-xs font-black text-amber-700">php artisan migrate</code> ครบทุกตารางของระบบยืมอุปกรณ์แล้ว
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active Borrows</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['active_borrows']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pending Requests</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['pending_requests']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Overdue Items</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['overdue_items']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pending Fines</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['pending_fine_amount'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-900 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
            <div class="bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.24),transparent_40%)] p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <i class="fa-solid fa-wallet text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/45">Collected This Month</p>
                        <h4 class="text-2xl font-black text-white">{{ number_format($stats['collected_this_month'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[1.3fr,0.9fr]">
        <div class="overflow-hidden rounded-[2.25rem] border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
                <div>
                    <h3 class="font-black text-slate-900">Recent Borrow Activity</h3>
                    <p class="mt-1 text-sm font-bold text-slate-500">รายการยืมล่าสุดที่ถูกเปิดใช้งานแล้ว</p>
                </div>
                @if (! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage'))
                    <a href="{{ route('admin.walk_in_borrow') }}" class="rounded-2xl bg-indigo-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-600">Open Walk-In</a>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentTransactions as $record)
                    <div class="flex items-center justify-between gap-4 px-8 py-5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-900">{{ $record->borrower?->full_name ?: $record->borrower?->name ?: 'Unknown user' }}</p>
                            <p class="mt-1 truncate text-xs font-bold uppercase tracking-widest text-slate-400">
                                {{ $record->item?->name ?? $record->category?->name ?? '-' }} · {{ $record->borrower?->identity_value ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-slate-700">{{ optional($record->borrowed_at)->format('d M Y') ?: '-' }}</p>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $record->approval_status }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-60">ยังไม่มีรายการยืมล่าสุด</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-[2.25rem] border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
                <div>
                    <h3 class="font-black text-slate-900">Need Attention</h3>
                    <p class="mt-1 text-sm font-bold text-slate-500">ของเกินกำหนดหรือมีค่าปรับค้างที่ควรตามต่อ</p>
                </div>
                @if (! $adminUser || $adminUser->hasActionAccess('borrow.return.process'))
                    <a href="{{ route('admin.borrow_returns') }}" class="rounded-2xl bg-rose-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-rose-600">Go To Returns</a>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($attentionItems as $record)
                    <div class="px-8 py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-900">{{ $record->borrower?->full_name ?: $record->borrower?->name ?: 'Unknown user' }}</p>
                                <p class="mt-1 truncate text-xs font-bold uppercase tracking-widest text-slate-400">
                                    {{ $record->item?->name ?? $record->category?->name ?? '-' }}
                                </p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $this->overdueDays($record) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600' }}">
                                {{ $this->overdueDays($record) > 0 ? 'Overdue '.$this->overdueDays($record).' days' : 'Fine pending' }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm font-bold">
                            <span class="text-slate-400">Due date</span>
                            <span class="text-slate-700">{{ $record->due_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-60">ไม่มีรายการที่ต้องติดตามเป็นพิเศษ</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
