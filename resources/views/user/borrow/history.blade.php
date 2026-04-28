<x-user-layout>
    <x-slot name="title">Borrow History - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">ประวัติการยืม</h2>
            <p class="text-xs font-medium text-slate-500">ติดตามสถานะคำขอ อุปกรณ์ที่กำลังยืม และรายการที่คืนแล้ว</p>
        </div>

        @if(session('message'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-amber-100 bg-white p-4 text-center shadow-sm">
                <p class="text-xl font-black text-amber-600">{{ $stats['pending'] }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pending</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 text-center shadow-sm">
                <p class="text-xl font-black text-emerald-600">{{ $stats['active'] }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Borrowed</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-white p-4 text-center shadow-sm">
                <p class="text-xl font-black text-sky-600">{{ $stats['returned'] }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Returned</p>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($records as $record)
                @php
                    $approvalMap = [
                        'pending' => ['label' => 'รอตรวจสอบ', 'class' => 'bg-amber-100 text-amber-700'],
                        'approved' => ['label' => $record->status === 'returned' ? 'คืนแล้ว' : 'อนุมัติแล้ว', 'class' => $record->status === 'returned' ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700'],
                        'rejected' => ['label' => 'ปฏิเสธคำขอ', 'class' => 'bg-rose-100 text-rose-700'],
                        'staff_added' => ['label' => 'บันทึกโดยเจ้าหน้าที่', 'class' => 'bg-slate-100 text-slate-700'],
                    ];
                    $status = $approvalMap[$record->approval_status] ?? $approvalMap['pending'];
                    $fine = $record->fines->first();
                @endphp

                <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                {{ $record->category?->name ?? 'Borrow Item' }}
                            </p>
                            <h3 class="mt-1 text-base font-black text-slate-900">
                                {{ $record->item?->name ?? ($record->category?->name ?? 'รายการยืม') }}
                            </h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-500">
                                {{ $record->reason ?: 'ไม่มีหมายเหตุเพิ่มเติม' }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $status['class'] }}">
                            {{ $status['label'] }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Borrowed</p>
                            <p class="mt-1 text-sm font-black text-slate-800">{{ $record->borrowed_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Due date</p>
                            <p class="mt-1 text-sm font-black text-slate-800">{{ $record->due_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Quantity</p>
                            <p class="mt-1 text-sm font-black text-slate-800">{{ $record->quantity }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Fine</p>
                            <p class="mt-1 text-sm font-black {{ $record->fine_status === 'pending' ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $fine ? number_format((float) $fine->amount, 2) . ' บาท' : 'ไม่มี' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-500">ยังไม่มีประวัติการยืมอุปกรณ์</p>
                </div>
            @endforelse
        </div>

        @if($records->hasPages())
            <div class="space-y-3">
                <p class="text-center text-xs font-bold text-slate-400">
                    หน้า {{ $records->currentPage() }} / {{ $records->lastPage() }} · รวม {{ $records->total() }} รายการ
                </p>
                <div class="flex justify-center">
                    {{ $records->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </main>
</x-user-layout>
