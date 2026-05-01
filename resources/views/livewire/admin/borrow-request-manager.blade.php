@php
    $adminUser = Auth::guard('admin')->user();
    $canApproveBorrowRequests = ! $adminUser || $adminUser->hasActionAccess('borrow.request.approve');
@endphp

<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm font-bold text-amber-700">
            ระบบ e-Borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน <code class="rounded bg-white px-2 py-1 text-xs font-black text-amber-700">php artisan migrate</code> เพื่อสร้างตาราง borrow ให้ครบก่อน
        </div>
    @endif

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div wire:click="$set('statusFilter', 'pending')" class="cursor-pointer rounded-[2rem] border border-emerald-100 bg-white p-6 transition-all hover:-translate-y-0.5 {{ $statusFilter === 'pending' ? 'ring-2 ring-amber-500 bg-amber-50/40' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-hourglass-half text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">รออนุมัติ</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['pending']) }}</h4>
                </div>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'approved')" class="cursor-pointer rounded-[2rem] border border-emerald-100 bg-white p-6 transition-all hover:-translate-y-0.5 {{ $statusFilter === 'approved' ? 'ring-2 ring-emerald-500 bg-emerald-50/40' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">อนุมัติแล้ว</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['approved']) }}</h4>
                </div>
            </div>
        </div>

        <div wire:click="$set('statusFilter', 'rejected')" class="cursor-pointer rounded-[2rem] border border-emerald-100 bg-white p-6 transition-all hover:-translate-y-0.5 {{ $statusFilter === 'rejected' ? 'ring-2 ring-rose-500 bg-rose-50/40' : '' }}">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-ban text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ปฏิเสธแล้ว</p>
                    <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['rejected']) }}</h4>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-cyan-700 bg-cyan-700 text-white">
            <div class="p-6">
                <div class="flex items-center gap-5">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-white">
                        <i class="fa-solid fa-box-open text-2xl"></i>
                    </div>
                    <div>
                        <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-white/70">กำลังยืมอยู่</p>
                        <h4 class="text-2xl font-black text-white">{{ number_format($stats['active']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[2.75rem] border border-emerald-100 bg-white p-7 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-3 rounded-full bg-cyan-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-cyan-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                    Borrow Queue
                </div>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">คำขอยืมอุปกรณ์</h2>
                <p class="mt-3 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                    ดูคำขอยืมทั้งหมด ค้นหาผู้ยืม เปิดรายละเอียดแบบ side drawer และอนุมัติหรือปฏิเสธได้ต่อเนื่องโดยไม่ต้องออกจากหน้านี้
                </p>
            </div>

            <div class="flex w-full flex-col gap-4 lg:max-w-2xl">
                <div class="flex w-full items-center gap-2 overflow-x-auto rounded-[1.5rem] bg-[#f7fbf8] p-1.5 lg:w-auto">
                    <button wire:click="$set('statusFilter', 'all')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ทั้งหมด</button>
                    <button wire:click="$set('statusFilter', 'pending')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">รออนุมัติ</button>
                    <button wire:click="$set('statusFilter', 'approved')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'approved' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">อนุมัติแล้ว</button>
                    <button wire:click="$set('statusFilter', 'rejected')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $statusFilter === 'rejected' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">ปฏิเสธแล้ว</button>
                </div>

                <div class="relative w-full">
                    <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="ค้นหาผู้ยืม รหัสระบุตัวตน หมวด หรือชื่ออุปกรณ์" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2.75rem] border border-emerald-100 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-7 py-6 lg:flex-row lg:items-end lg:justify-between lg:px-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Borrow Requests</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">รายการคำขอยืมทั้งหมด</h3>
            </div>
            <p class="text-sm font-bold text-slate-500">หน้า {{ $records->lastPage() > 0 ? $records->currentPage() : 0 }} / {{ $records->lastPage() }} · รวม {{ number_format($records->total()) }} รายการ</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f7fbf8] text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-7 py-5 lg:px-8">วันที่ขอ</th>
                        <th class="px-7 py-5 lg:px-8">ผู้ขอยืม</th>
                        <th class="px-7 py-5 lg:px-8">หมวด / อุปกรณ์</th>
                        <th class="px-7 py-5 lg:px-8">กำหนดคืน</th>
                        <th class="px-7 py-5 text-center lg:px-8">สถานะ</th>
                        <th class="px-7 py-5 text-right lg:px-8">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $record)
                        <tr class="transition-colors hover:bg-[#fbfefc]">
                            <td class="px-7 py-6 lg:px-8">
                                <div class="font-black text-slate-900">{{ optional($record->created_at)->format('d M Y') }}</div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ optional($record->created_at)->format('H:i') }}</div>
                            </td>
                            <td class="cursor-pointer px-7 py-6 lg:px-8" wire:click="openDetails({{ $record->id }})">
                                <div class="font-black text-slate-900 transition-colors hover:text-emerald-700">{{ $record->borrower?->full_name ?: $record->borrower?->name ?: 'Unknown user' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                    {{ $record->borrower?->identity_label ?? 'Identity' }} · {{ $record->borrower?->identity_value ?? '-' }}
                                </div>
                            </td>
                            <td class="px-7 py-6 lg:px-8">
                                <div class="font-black text-slate-900">{{ $record->category?->name ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $record->item?->name ?? 'รอเลือกอุปกรณ์' }}</div>
                            </td>
                            <td class="px-7 py-6 lg:px-8">
                                <div class="font-black text-slate-900">{{ $record->due_date?->format('d M Y') ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $record->quantity }} ชิ้น</div>
                            </td>
                            <td class="px-7 py-6 text-center lg:px-8">
                                <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em]
                                    {{ $record->approval_status === 'pending' ? 'border border-amber-200 bg-amber-50 text-amber-700' : '' }}
                                    {{ $record->approval_status === 'approved' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $record->approval_status === 'rejected' ? 'border border-rose-200 bg-rose-50 text-rose-700' : '' }}">
                                    {{ $record->approval_status }}
                                </span>
                            </td>
                            <td class="px-7 py-6 text-right lg:px-8">
                                <div class="inline-flex items-center gap-2">
                                    @if($canApproveBorrowRequests && $record->approval_status === 'pending')
                                        <button wire:click="approve({{ $record->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white transition-all hover:bg-emerald-700">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button wire:click="reject({{ $record->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition-all hover:bg-rose-100">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDetails({{ $record->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-700">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-7 py-24 text-center lg:px-8">
                                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                                    <i class="fa-solid fa-boxes-packing text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-slate-400">ยังไม่มีคำขอยืมในตอนนี้</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 bg-[#f7fbf8] px-7 py-5 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm font-bold text-slate-500">หน้า {{ $records->lastPage() > 0 ? $records->currentPage() : 0 }} / {{ $records->lastPage() }} · รวม {{ number_format($records->total()) }} รายการ</p>
                @if($records->hasPages())
                    {{ $records->links() }}
                @endif
            </div>
        </div>
    </section>

    @if($showDrawer && $selectedRecordDetails)
        <div class="fixed inset-0 z-[120] flex items-center justify-end overflow-hidden">
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" wire:click="closeDrawer"></div>

            <aside class="relative flex h-full w-full max-w-xl flex-col overflow-hidden border-l border-emerald-100 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                <div class="border-b border-slate-100 px-8 py-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Borrow Request</p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">รายละเอียดคำขอยืม</h3>
                            <p class="mt-2 text-sm font-bold text-slate-500">Record #{{ $selectedRecordDetails->id }}</p>
                        </div>
                        <button wire:click="closeDrawer" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 space-y-8 overflow-y-auto px-8 py-8">
                    <section class="rounded-[2rem] border border-emerald-100 bg-[#f7fbf8] p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">ผู้ขอยืม</p>
                        <div class="mt-5 flex items-center gap-5">
                            <div class="flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-white text-3xl text-slate-300 shadow-inner">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h4 class="text-3xl font-black tracking-tight text-slate-950">{{ $selectedRecordDetails->borrower?->full_name ?: $selectedRecordDetails->borrower?->name ?: 'Unknown user' }}</h4>
                                <p class="mt-2 text-sm font-bold text-slate-500">{{ $selectedRecordDetails->borrower?->identity_label ?? 'Identity' }} · {{ $selectedRecordDetails->borrower?->identity_value ?? '-' }}</p>
                            </div>
                        </div>
                    </section>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เบอร์โทรศัพท์</p>
                            <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedRecordDetails->borrower?->phone_number ?? '-' }}</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">คณะ / หน่วยงาน</p>
                            <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedRecordDetails->borrower?->department ?? '-' }}</p>
                        </div>
                    </div>

                    <section class="overflow-hidden rounded-[2.25rem] border border-cyan-200 bg-cyan-600 text-white">
                        <div class="p-7">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/70">รายละเอียดคำขอ</p>
                            <div class="mt-5 space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-bold text-white/80">หมวดอุปกรณ์</span>
                                    <span class="text-lg font-black">{{ $selectedRecordDetails->category?->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-bold text-white/80">อุปกรณ์ที่จัดให้</span>
                                    <span class="text-lg font-black">{{ $selectedRecordDetails->item?->name ?? 'รอเลือกอุปกรณ์' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-bold text-white/80">กำหนดคืน</span>
                                    <span class="text-lg font-black">{{ $selectedRecordDetails->due_date?->format('d M Y') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เหตุผลในการยืม</p>
                        <p class="mt-4 text-sm font-bold leading-7 text-slate-700">{{ $selectedRecordDetails->reason ?: '-' }}</p>
                    </section>

                    @if($selectedRecordDetails->notes)
                        <section class="rounded-[2rem] border border-slate-200 bg-white p-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">บันทึกเพิ่มเติม</p>
                            <pre class="mt-4 whitespace-pre-wrap text-sm font-bold leading-7 text-slate-700">{{ $selectedRecordDetails->notes }}</pre>
                        </section>
                    @endif
                </div>

                <div class="border-t border-slate-100 bg-[#f7fbf8] px-8 py-6">
                    <div class="flex gap-3">
                        @if($canApproveBorrowRequests && $selectedRecordDetails->approval_status === 'pending')
                            <button wire:click="approve({{ $selectedRecordDetails->id }})" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">
                                อนุมัติคำขอยืม
                            </button>
                            <button wire:click="reject({{ $selectedRecordDetails->id }})" class="flex-1 rounded-2xl border border-rose-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-rose-700 transition-all hover:bg-rose-50">
                                ปฏิเสธคำขอยืม
                            </button>
                        @else
                            <button wire:click="closeDrawer" class="w-full rounded-2xl bg-slate-950 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-slate-800">
                                ปิดหน้าต่าง
                            </button>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
