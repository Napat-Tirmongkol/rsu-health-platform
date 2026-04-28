<div class="space-y-8 animate-in fade-in duration-700">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2.5rem] border border-amber-100 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            ระบบ e-Borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน `php artisan migrate` เพื่อสร้างตาราง borrow ก่อน
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div wire:click="$set('statusFilter', 'pending')" class="cursor-pointer rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-all hover:shadow-md {{ $statusFilter === 'pending' ? 'ring-2 ring-amber-500 bg-amber-50/30' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 shadow-inner">
                    <i class="fa-solid fa-hourglass-half text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">รออนุมัติ</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['pending']) }}</h4>
                </div>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'approved')" class="cursor-pointer rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-all hover:shadow-md {{ $statusFilter === 'approved' ? 'ring-2 ring-emerald-500 bg-emerald-50/30' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 shadow-inner">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">อนุมัติแล้ว</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['approved']) }}</h4>
                </div>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'rejected')" class="cursor-pointer rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-all hover:shadow-md {{ $statusFilter === 'rejected' ? 'ring-2 ring-rose-500 bg-rose-50/30' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 shadow-inner">
                    <i class="fa-solid fa-ban text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ปฏิเสธแล้ว</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['rejected']) }}</h4>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 p-6 shadow-xl">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/5 blur-2xl"></div>
            <div class="relative flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white shadow-inner">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-white/40">กำลังยืมอยู่</p>
                    <h4 class="text-2xl font-black text-white">{{ number_format($stats['active']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col items-stretch gap-6 rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex w-full items-center gap-2 overflow-x-auto rounded-[1.5rem] bg-slate-50 p-1.5 lg:w-auto">
            <button wire:click="$set('statusFilter', 'all')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ทั้งหมด</button>
            <button wire:click="$set('statusFilter', 'pending')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">รออนุมัติ</button>
            <button wire:click="$set('statusFilter', 'approved')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'approved' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">อนุมัติแล้ว</button>
            <button wire:click="$set('statusFilter', 'rejected')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'rejected' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ปฏิเสธแล้ว</button>
        </div>

        <div class="relative w-full lg:max-w-md">
            <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="ค้นหาชื่อผู้ยืม, รหัส, หมวด, อุปกรณ์..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
        </div>
    </div>

    <div class="overflow-hidden rounded-[3rem] border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-8 py-6">วันที่ขอ</th>
                        <th class="px-8 py-6">ผู้ขอยืม</th>
                        <th class="px-8 py-6">หมวด / อุปกรณ์</th>
                        <th class="px-8 py-6">กำหนดคืน</th>
                        <th class="px-8 py-6 text-center">สถานะ</th>
                        <th class="px-8 py-6 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($records as $record)
                        <tr class="group hover:bg-slate-50/40 transition-all">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-800">{{ optional($record->created_at)->format('d M Y') }}</div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ optional($record->created_at)->format('H:i') }}</div>
                            </td>
                            <td class="cursor-pointer px-8 py-6" wire:click="openDetails({{ $record->id }})">
                                <div class="font-black text-slate-800 group-hover:text-[#2e9e63] transition-colors">{{ $record->borrower?->full_name ?: $record->borrower?->name ?: 'Unknown user' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                    {{ $record->borrower?->identity_label ?? 'Identity' }}: {{ $record->borrower?->identity_value ?? '-' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-800">{{ $record->category?->name ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $record->item?->name ?? 'รอเลือกอุปกรณ์' }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-800">{{ $record->due_date?->format('d M Y') ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $record->quantity }} ชิ้น</div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-widest
                                    {{ $record->approval_status === 'pending' ? 'border border-amber-100 bg-amber-50 text-amber-600' : '' }}
                                    {{ $record->approval_status === 'approved' ? 'border border-emerald-100 bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $record->approval_status === 'rejected' ? 'border border-rose-100 bg-rose-50 text-rose-600' : '' }}">
                                    {{ $record->approval_status }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                    @if($record->approval_status === 'pending')
                                        <button wire:click="approve({{ $record->id }})" class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2e9e63] text-white shadow-lg shadow-green-100 transition-all hover:scale-110 active:scale-95">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button wire:click="reject({{ $record->id }})" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-white text-rose-500 shadow-sm transition-all hover:scale-110 hover:bg-rose-50 active:scale-95">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDetails({{ $record->id }})" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center opacity-40">
                                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-boxes-packing text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-slate-400">ยังไม่มีคำขอยืมในตอนนี้</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-50 bg-slate-50/30 px-8 py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm font-bold text-slate-500">
                    หน้า {{ $records->lastPage() > 0 ? $records->currentPage() : 0 }} / {{ $records->lastPage() }} · รวม {{ number_format($records->total()) }} รายการ
                </p>
                @if($records->hasPages())
                    {{ $records->links() }}
                @endif
            </div>
        </div>
    </div>

    @if($showDrawer && $selectedRecordDetails)
        <div class="fixed inset-0 z-[120] flex items-center justify-end overflow-hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeDrawer"></div>

            <aside class="relative flex h-full w-full max-w-xl flex-col bg-white shadow-2xl animate-in slide-in-from-right duration-500">
                <div class="flex items-center justify-between border-b border-slate-50 p-10">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight text-slate-800">Borrow Request</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Record #{{ $selectedRecordDetails->id }}</p>
                    </div>
                    <button wire:click="closeDrawer" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="custom-scrollbar flex-1 space-y-8 overflow-y-auto p-10">
                    <div class="space-y-4">
                        <span class="rounded-lg bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700">ข้อมูลผู้ยืม</span>
                        <div class="flex items-center gap-6">
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl border border-slate-100 bg-slate-50 text-3xl text-slate-300 shadow-inner">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 text-3xl font-black leading-tight text-slate-800">{{ $selectedRecordDetails->borrower?->full_name ?: $selectedRecordDetails->borrower?->name ?: 'Unknown user' }}</h4>
                                <p class="text-sm font-bold tracking-widest text-slate-400">{{ $selectedRecordDetails->borrower?->identity_label ?? 'Identity' }}: {{ $selectedRecordDetails->borrower?->identity_value ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6">
                            <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">เบอร์โทรศัพท์</label>
                            <p class="text-lg font-black text-slate-800">{{ $selectedRecordDetails->borrower?->phone_number ?? '-' }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-100 bg-slate-50 p-6">
                            <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">คณะ / หน่วยงาน</label>
                            <p class="text-lg font-black text-slate-800">{{ $selectedRecordDetails->borrower?->department ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="rounded-[3rem] bg-[#2e9e63] p-8 text-white shadow-xl shadow-green-100">
                        <label class="mb-4 block text-[10px] font-black uppercase tracking-widest text-white/50">รายละเอียดคำขอ</label>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-white/70">หมวดอุปกรณ์</span>
                                <span class="text-lg font-black">{{ $selectedRecordDetails->category?->name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-white/70">อุปกรณ์ที่จัดให้</span>
                                <span class="text-lg font-black">{{ $selectedRecordDetails->item?->name ?? 'รอเลือกอุปกรณ์' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-white/70">กำหนดคืน</span>
                                <span class="text-lg font-black">{{ $selectedRecordDetails->due_date?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2.5rem] border border-slate-100 bg-slate-50 p-8">
                        <label class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-400">เหตุผลในการยืม</label>
                        <p class="text-sm font-bold leading-7 text-slate-700">{{ $selectedRecordDetails->reason ?: '-' }}</p>
                    </div>

                    @if($selectedRecordDetails->notes)
                        <div class="rounded-[2.5rem] border border-slate-100 bg-white p-8 shadow-sm">
                            <label class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-400">บันทึกเพิ่มเติม</label>
                            <pre class="whitespace-pre-wrap text-sm font-bold leading-7 text-slate-700">{{ $selectedRecordDetails->notes }}</pre>
                        </div>
                    @endif
                </div>

                <div class="flex gap-4 border-t border-slate-100 bg-slate-50 p-10">
                    @if($selectedRecordDetails->approval_status === 'pending')
                        <button wire:click="approve({{ $selectedRecordDetails->id }})" class="flex-1 rounded-2xl bg-[#2e9e63] px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-green-100 transition-all active:scale-95">
                            อนุมัติคำขอยืม
                        </button>
                        <button wire:click="reject({{ $selectedRecordDetails->id }})" class="flex-1 rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-rose-500 transition-all active:scale-95">
                            ปฏิเสธคำขอยืม
                        </button>
                    @else
                        <button wire:click="closeDrawer" class="w-full rounded-2xl bg-slate-900 px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-slate-200 transition-all active:scale-95">
                            ปิดหน้าต่าง
                        </button>
                    @endif
                </div>
            </aside>
        </div>
    @endif
</div>
