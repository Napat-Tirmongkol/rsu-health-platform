@php
    $adminUser = Auth::guard('admin')->user();
    $canManageBookings = ! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage');
    $statusMeta = [
        'pending' => ['label' => 'รออนุมัติ', 'badge' => 'bg-amber-50 text-amber-700 border border-amber-200', 'icon' => 'fa-clock'],
        'confirmed' => ['label' => 'ยืนยันแล้ว', 'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'icon' => 'fa-circle-check'],
        'cancelled' => ['label' => 'ยกเลิกแล้ว', 'badge' => 'bg-rose-50 text-rose-700 border border-rose-200', 'icon' => 'fa-circle-xmark'],
    ];
@endphp

<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <button wire:click="$set('statusFilter', 'pending')" class="rounded-[2rem] border p-6 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md {{ $statusFilter === 'pending' ? 'border-amber-300 bg-amber-50/80' : 'border-slate-200 bg-white' }}">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400 shadow-sm">Queue</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">รออนุมัติ</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['pending']) }}</h3>
            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-500">รายการที่ยังต้องตัดสินใจจากทีมคลินิก</p>
        </button>

        <button wire:click="$set('statusFilter', 'confirmed')" class="rounded-[2rem] border p-6 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md {{ $statusFilter === 'confirmed' ? 'border-emerald-300 bg-emerald-50/80' : 'border-slate-200 bg-white' }}">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400 shadow-sm">Live</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">ยืนยันแล้ว</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['confirmed']) }}</h3>
            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-500">คิวที่พร้อมรับบริการตามแคมเปญและรอบเวลา</p>
        </button>

        <button wire:click="$set('statusFilter', 'cancelled')" class="rounded-[2rem] border p-6 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md {{ $statusFilter === 'cancelled' ? 'border-rose-300 bg-rose-50/80' : 'border-slate-200 bg-white' }}">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <i class="fa-solid fa-circle-xmark text-xl"></i>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400 shadow-sm">Alert</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">ยกเลิกแล้ว</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['cancelled']) }}</h3>
            <p class="mt-3 text-sm font-bold leading-relaxed text-slate-500">รายการที่ถูกปิดหรือแจ้งยกเลิกจากฝั่งงานบริการ</p>
        </button>

        <div class="overflow-hidden rounded-[2rem] border border-slate-900 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
            <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.22),transparent_40%)] p-6">
                <div class="flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <i class="fa-solid fa-calendar-day text-xl"></i>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-white/55">Today</span>
                </div>
                <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-white/45">นัดหมายวันนี้</p>
                <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ number_format($stats['today']) }}</h3>
                <p class="mt-3 text-sm font-bold leading-relaxed text-white/72">ภาพรวมคิวที่ผูกกับรอบเวลาของวันนี้ทั้งหมด</p>
            </div>
        </div>
    </section>

    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm xl:p-7">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Filters & Search</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Booking Command Center</h2>
                <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-slate-500">กรองสถานะ ค้นหาผู้จอง หรือเปิดรายละเอียดแบบ side drawer เพื่อจัดการคิวได้ต่อเนื่องโดยไม่ต้องเปลี่ยนหน้า</p>
            </div>

            <div class="flex w-full flex-col gap-4 xl:max-w-2xl">
                <div class="flex flex-wrap gap-2">
                    @foreach (['all' => 'ทั้งหมด', 'pending' => 'รออนุมัติ', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิกแล้ว'] as $key => $label)
                        <button wire:click="$set('statusFilter', '{{ $key }}')" class="rounded-2xl px-5 py-3 text-[11px] font-black uppercase tracking-[0.2em] transition-all {{ $statusFilter === $key ? 'bg-slate-950 text-white shadow-lg shadow-slate-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="relative">
                    <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input wire:model.live="search" type="text" placeholder="ค้นหาจากชื่อ รหัสผู้ใช้ เลขระบุตัวตน หรือชื่อแคมเปญ" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2.75rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-6 xl:flex-row xl:items-center xl:justify-between xl:px-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Booking Queue</p>
                <h3 class="mt-2 text-xl font-black text-slate-950">รายการจองทั้งหมด</h3>
            </div>
            <p class="text-sm font-bold text-slate-500">หน้า {{ $bookings->lastPage() > 0 ? $bookings->currentPage() : 0 }} / {{ $bookings->lastPage() }} · รวม {{ number_format($bookings->total()) }} รายการ</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5 text-center xl:px-8">
                            <input type="checkbox" class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="px-6 py-5 xl:px-8">วันและเวลา</th>
                        <th class="px-6 py-5 xl:px-8">ผู้จอง</th>
                        <th class="px-6 py-5 xl:px-8">แคมเปญ</th>
                        <th class="px-6 py-5 text-center xl:px-8">สถานะ</th>
                        <th class="px-6 py-5 text-right xl:px-8">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                        @php($meta = $statusMeta[$b->status] ?? ['label' => strtoupper($b->status), 'badge' => 'bg-slate-100 text-slate-600 border border-slate-200', 'icon' => 'fa-circle'])
                        <tr class="group transition-colors hover:bg-slate-50/80">
                            <td class="px-6 py-5 text-center xl:px-8">
                                <input type="checkbox" wire:model.live="selectedBookings" value="{{ $b->id }}" class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-6 py-5 xl:px-8">
                                <div class="font-black text-slate-900">{{ $b->slot ? $b->slot->date->format('d M Y') : 'N/A' }}</div>
                                <div class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-emerald-600">
                                    {{ $b->slot ? substr($b->slot->start_time, 0, 5) . ' - ' . substr($b->slot->end_time, 0, 5) : '-' }}
                                </div>
                            </td>
                            <td class="cursor-pointer px-6 py-5 xl:px-8" wire:click="openDetails({{ $b->id }})">
                                <div class="font-black text-slate-900 transition-colors group-hover:text-emerald-700">{{ $b->user->full_name }}</div>
                                <div class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $b->user->identity_label }} · {{ $b->user->identity_value }}</div>
                            </td>
                            <td class="px-6 py-5 xl:px-8">
                                <div class="max-w-[16rem] truncate text-sm font-black text-slate-800">{{ $b->campaign->title }}</div>
                                <div class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Code · {{ $b->booking_code }}</div>
                                @if($canManageBookings)
                                    <a href="{{ route('staff.scan.campaign', $b->campaign->id) }}" target="_blank" class="mt-3 inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600 transition-colors hover:text-emerald-700">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Open Scanner</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center xl:px-8">
                                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] {{ $meta['badge'] }}">
                                    <i class="fa-solid {{ $meta['icon'] }}"></i>
                                    <span>{{ $meta['label'] }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right xl:px-8">
                                <div class="inline-flex items-center gap-2">
                                    @if($canManageBookings && $b->status === 'pending')
                                        <button wire:click="approve({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-emerald-700">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button wire:click="cancel({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition-all hover:-translate-y-0.5 hover:bg-rose-100">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDetails({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-700">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center xl:px-8">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-calendar-xmark text-3xl"></i>
                                </div>
                                <p class="mt-5 text-sm font-bold uppercase tracking-[0.22em] text-slate-400">ไม่พบรายการจองในเงื่อนไขนี้</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-5 xl:px-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>

    @if($canManageBookings && count($selectedBookings) > 0)
        <div class="fixed bottom-10 left-1/2 z-[110] flex -translate-x-1/2 items-center gap-8 rounded-[2.5rem] border border-slate-800 bg-slate-950 px-8 py-5 text-white shadow-[0_24px_80px_rgba(15,23,42,0.34)]">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-400">Selected Rows</p>
                <p class="mt-1 text-sm font-black">{{ count($selectedBookings) }} รายการ</p>
            </div>
            <div class="h-10 w-px bg-white/10"></div>
            <div class="flex items-center gap-3">
                <button wire:click="bulkApprove" class="rounded-2xl bg-emerald-600 px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">อนุมัติทั้งหมด</button>
                <button wire:click="bulkCancel" class="rounded-2xl bg-rose-600 px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-rose-700">ยกเลิกทั้งหมด</button>
                <button wire:click="$set('selectedBookings', [])" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition-all hover:bg-white/10 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    @if($showDrawer && $selectedBookingDetails)
        <div class="fixed inset-0 z-[120] flex items-center justify-end overflow-hidden">
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" wire:click="closeDrawer"></div>

            <aside class="relative flex h-full w-full max-w-2xl flex-col overflow-hidden border-l border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="border-b border-slate-100 px-8 py-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Booking Detail</p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">รายละเอียดการจอง</h3>
                            <p class="mt-2 text-sm font-bold text-slate-500">รหัสการจอง · {{ $selectedBookingDetails->booking_code }}</p>
                        </div>
                        <button wire:click="closeDrawer" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-8 py-8">
                    <div class="space-y-8">
                        <section class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">ผู้จอง</p>
                            <div class="mt-5 flex items-center gap-5">
                                <div class="flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-white text-3xl text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="text-3xl font-black tracking-tight text-slate-950">{{ $selectedBookingDetails->user->full_name }}</h4>
                                    <p class="mt-2 text-sm font-bold text-slate-500">{{ $selectedBookingDetails->user->identity_label }} · {{ $selectedBookingDetails->user->identity_value }}</p>
                                </div>
                            </div>
                        </section>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เบอร์โทรศัพท์</p>
                                <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedBookingDetails->user->phone_number ?? '-' }}</p>
                            </div>
                            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">คณะ / หน่วยงาน</p>
                                <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedBookingDetails->user->department ?? '-' }}</p>
                            </div>
                        </div>

                        <section class="overflow-hidden rounded-[2.25rem] border border-emerald-200 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.16)]">
                            <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.28),transparent_44%)] p-7">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">วันและเวลา</p>
                                <div class="mt-5 flex items-center gap-5">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-2xl text-white">
                                        <i class="fa-regular fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-black">{{ $selectedBookingDetails->slot ? $selectedBookingDetails->slot->date->format('d F Y') : 'N/A' }}</p>
                                        <p class="mt-1 text-sm font-bold text-white/72">{{ $selectedBookingDetails->slot ? substr($selectedBookingDetails->slot->start_time, 0, 5) . ' - ' . substr($selectedBookingDetails->slot->end_time, 0, 5) : '-' }} น.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[2rem] border border-slate-200 bg-white p-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">แคมเปญ</p>
                            <div class="mt-4 flex items-center gap-4 rounded-[1.75rem] bg-slate-50 p-5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <p class="text-base font-black text-slate-900">{{ $selectedBookingDetails->campaign->title }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Status · {{ $statusMeta[$selectedBookingDetails->status]['label'] ?? $selectedBookingDetails->status }}</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-8 py-6">
                    <div class="flex flex-wrap gap-3">
                        @if($canManageBookings)
                            <a href="{{ route('staff.scan.campaign', $selectedBookingDetails->campaign->id) }}" target="_blank" class="inline-flex h-14 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 text-xs font-black uppercase tracking-[0.18em] text-emerald-700 transition-all hover:bg-emerald-50">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Open Scanner</span>
                            </a>
                        @endif
                        @if($canManageBookings && $selectedBookingDetails->status === 'pending')
                            <button wire:click="approve({{ $selectedBookingDetails->id }})" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">ยืนยันการจอง</button>
                            <button wire:click="cancel({{ $selectedBookingDetails->id }})" class="flex-1 rounded-2xl border border-rose-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-rose-600 transition-all hover:bg-rose-50">ยกเลิก</button>
                        @else
                            <button wire:click="closeDrawer" class="w-full rounded-2xl bg-slate-950 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-slate-800">ปิดหน้าต่าง</button>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
