@php
    $adminUser = Auth::guard('admin')->user();
    $canManageBookings = ! $adminUser || $adminUser->hasActionAccess('campaign.booking.manage');
    $statusMeta = [
        'pending' => ['label' => 'เธฃเธญเธญเธเธธเธกเธฑเธ•เธด', 'badge' => 'bg-amber-50 text-amber-700 border border-amber-200', 'icon' => 'fa-clock'],
        'confirmed' => ['label' => 'เธขเธทเธเธขเธฑเธเนเธฅเนเธง', 'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'icon' => 'fa-circle-check'],
        'cancelled' => ['label' => 'เธขเธเน€เธฅเธดเธเนเธฅเนเธง', 'badge' => 'bg-rose-50 text-rose-700 border border-rose-200', 'icon' => 'fa-circle-xmark'],
    ];
@endphp

<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <button wire:click="$set('statusFilter', 'pending')" class="text-left transition-all hover:-translate-y-0.5">
            <x-admin.stat-card icon="fa-clock" badge="Queue" eyebrow="รออนุมัติ" :value="number_format($stats['pending'])" description="รายการที่ยังต้องตัดสินใจจากทีมคลินิกก่อนเปิดสิทธิ์เข้ารับบริการ" variant="{{ $statusFilter === 'pending' ? 'warning' : 'default' }}" />
        </button>

        <button wire:click="$set('statusFilter', 'confirmed')" class="text-left transition-all hover:-translate-y-0.5">
            <x-admin.stat-card icon="fa-circle-check" badge="Confirmed" eyebrow="ยืนยันแล้ว" :value="number_format($stats['confirmed'])" description="คิวที่พร้อมเข้ารับบริการตามรอบเวลาและแคมเปญที่กำหนดไว้" variant="{{ $statusFilter === 'confirmed' ? 'success' : 'default' }}" />
        </button>

        <button wire:click="$set('statusFilter', 'cancelled')" class="text-left transition-all hover:-translate-y-0.5">
            <x-admin.stat-card icon="fa-circle-xmark" badge="Closed" eyebrow="ยกเลิกแล้ว" :value="number_format($stats['cancelled'])" description="รายการที่ยกเลิกแล้วเพื่อใช้ติดตามการประสานงานย้อนหลัง" variant="{{ $statusFilter === 'cancelled' ? 'danger' : 'default' }}" />
        </button>

        <x-admin.stat-card icon="fa-calendar-day" badge="Today" eyebrow="นัดหมายวันนี้" :value="number_format($stats['today'])" description="ภาพรวมคิวของวันนี้เพื่อช่วยทีมหน้าบ้านเตรียมการเรียกคิวและตรวจสอบหน้างาน" variant="soft-accent" />
    </section>

    <section class="rounded-[2.75rem] border border-emerald-100 bg-white p-7 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="inline-flex items-center gap-3 rounded-full bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Booking Command Center
                </div>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">เธเธฑเธ”เธเธฒเธฃเธฃเธฒเธขเธเธฒเธฃเธเธญเธเธ—เธฑเนเธเธซเธกเธ”</h2>
                <p class="mt-3 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                    เธเธฃเธญเธเธ•เธฒเธกเธชเธ–เธฒเธเธฐ เธเนเธเธซเธฒเธเธนเนเธเธญเธ เน€เธเธดเธ”เธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”เนเธเธ side drawer เนเธฅเธฐเธขเธทเธเธขเธฑเธเธซเธฃเธทเธญเธขเธเน€เธฅเธดเธเธฃเธฒเธขเธเธฒเธฃเนเธ”เนเธ•เนเธญเน€เธเธทเนเธญเธเนเธ”เธขเนเธกเนเธ•เนเธญเธเธชเธฅเธฑเธเธซเธเนเธฒ
                </p>
            </div>

            <div class="flex w-full flex-col gap-4 xl:max-w-2xl">
                <div class="flex flex-wrap gap-2">
                    @foreach (['all' => 'เธ—เธฑเนเธเธซเธกเธ”', 'pending' => 'เธฃเธญเธญเธเธธเธกเธฑเธ•เธด', 'confirmed' => 'เธขเธทเธเธขเธฑเธเนเธฅเนเธง', 'cancelled' => 'เธขเธเน€เธฅเธดเธเนเธฅเนเธง'] as $key => $label)
                        <button wire:click="$set('statusFilter', '{{ $key }}')" class="rounded-2xl px-5 py-3 text-[11px] font-black uppercase tracking-[0.2em] transition-all {{ $statusFilter === $key ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="relative">
                    <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input
                        wire:model.live="search"
                        type="text"
                        placeholder="เธเนเธเธซเธฒเธเธฒเธเธเธทเนเธญเธเธนเนเธเธญเธ เธฃเธซเธฑเธชเธฃเธฐเธเธธเธ•เธฑเธงเธ•เธ เธซเธฃเธทเธญเธเธทเนเธญเนเธเธกเน€เธเธ"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                    >
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2.75rem] border border-emerald-100 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-7 py-6 lg:flex-row lg:items-end lg:justify-between lg:px-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Booking Queue</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">เธฃเธฒเธขเธเธฒเธฃเธเธญเธเธ—เธฑเนเธเธซเธกเธ”</h3>
            </div>
            <p class="text-sm font-bold text-slate-500">
                เธซเธเนเธฒ {{ $bookings->lastPage() > 0 ? $bookings->currentPage() : 0 }} / {{ $bookings->lastPage() }} ยท เธฃเธงเธก {{ number_format($bookings->total()) }} เธฃเธฒเธขเธเธฒเธฃ
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f7fbf8] text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5 text-center lg:px-8">
                            <input type="checkbox" class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="px-6 py-5 lg:px-8">เธงเธฑเธเนเธฅเธฐเน€เธงเธฅเธฒ</th>
                        <th class="px-6 py-5 lg:px-8">เธเธนเนเธเธญเธ</th>
                        <th class="px-6 py-5 lg:px-8">เนเธเธกเน€เธเธ</th>
                        <th class="px-6 py-5 text-center lg:px-8">เธชเธ–เธฒเธเธฐ</th>
                        <th class="px-6 py-5 text-right lg:px-8">เธเธฒเธฃเธเธฑเธ”เธเธฒเธฃ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                        @php($meta = $statusMeta[$b->status] ?? ['label' => strtoupper($b->status), 'badge' => 'bg-slate-100 text-slate-600 border border-slate-200', 'icon' => 'fa-circle'])
                        <tr class="transition-colors hover:bg-[#fbfefc]">
                            <td class="px-6 py-5 text-center lg:px-8">
                                <input type="checkbox" wire:model.live="selectedBookings" value="{{ $b->id }}" class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-6 py-5 lg:px-8">
                                <div class="font-black text-slate-900">{{ $b->slot ? $b->slot->date->format('d M Y') : 'N/A' }}</div>
                                <div class="mt-1 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                                    {{ $b->slot ? substr($b->slot->start_time, 0, 5) . ' - ' . substr($b->slot->end_time, 0, 5) : '-' }}
                                </div>
                            </td>
                            <td class="cursor-pointer px-6 py-5 lg:px-8" wire:click="openDetails({{ $b->id }})">
                                <div class="font-black text-slate-900 transition-colors hover:text-emerald-700">{{ $b->user->full_name }}</div>
                                <div class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $b->user->identity_label }} ยท {{ $b->user->identity_value }}</div>
                            </td>
                            <td class="px-6 py-5 lg:px-8">
                                <div class="max-w-[16rem] truncate text-sm font-black text-slate-900">{{ $b->campaign->title }}</div>
                                <div class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Code ยท {{ $b->booking_code }}</div>
                                @if($canManageBookings)
                                    <a href="{{ route('staff.scan.campaign', $b->campaign->id) }}" target="_blank" class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700 transition-all hover:bg-emerald-100">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Open Scanner</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center lg:px-8">
                                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] {{ $meta['badge'] }}">
                                    <i class="fa-solid {{ $meta['icon'] }}"></i>
                                    <span>{{ $meta['label'] }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right lg:px-8">
                                <div class="inline-flex items-center gap-2">
                                    @if($canManageBookings && $b->status === 'pending')
                                        <button wire:click="approve({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white transition-all hover:bg-emerald-700">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button wire:click="cancel({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition-all hover:bg-rose-100">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openDetails({{ $b->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-700">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center lg:px-8">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                                    <i class="fa-solid fa-calendar-xmark text-3xl"></i>
                                </div>
                                <p class="mt-5 text-sm font-bold uppercase tracking-[0.22em] text-slate-400">เนเธกเนเธเธเธฃเธฒเธขเธเธฒเธฃเธเธญเธเนเธเน€เธเธทเนเธญเธเนเธเธเธตเน</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="border-t border-slate-100 bg-[#f7fbf8] px-7 py-5 lg:px-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>

    @if($canManageBookings && count($selectedBookings) > 0)
        <div class="fixed bottom-10 left-1/2 z-[110] flex -translate-x-1/2 items-center gap-6 rounded-[2.5rem] border border-emerald-700 bg-white px-8 py-5 shadow-[0_24px_80px_rgba(16,185,129,0.18)]">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-600">Selected Rows</p>
                <p class="mt-1 text-sm font-black text-slate-900">{{ count($selectedBookings) }} เธฃเธฒเธขเธเธฒเธฃ</p>
            </div>
            <div class="h-10 w-px bg-slate-200"></div>
            <div class="flex items-center gap-3">
                <button wire:click="bulkApprove" class="rounded-2xl bg-emerald-600 px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">เธญเธเธธเธกเธฑเธ•เธดเธ—เธฑเนเธเธซเธกเธ”</button>
                <button wire:click="bulkCancel" class="rounded-2xl bg-rose-600 px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-rose-700">เธขเธเน€เธฅเธดเธเธ—เธฑเนเธเธซเธกเธ”</button>
                <button wire:click="$set('selectedBookings', [])" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-slate-100">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    @if($showDrawer && $selectedBookingDetails)
        <div class="fixed inset-0 z-[120] flex items-center justify-end overflow-hidden">
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" wire:click="closeDrawer"></div>

            <aside class="relative flex h-full w-full max-w-2xl flex-col overflow-hidden border-l border-emerald-100 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                <div class="border-b border-slate-100 px-8 py-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Booking Detail</p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">เธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”เธเธฒเธฃเธเธญเธ</h3>
                            <p class="mt-2 text-sm font-bold text-slate-500">เธฃเธซเธฑเธชเธเธฒเธฃเธเธญเธ ยท {{ $selectedBookingDetails->booking_code }}</p>
                        </div>
                        <button wire:click="closeDrawer" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-8 py-8">
                    <div class="space-y-8">
                        <section class="rounded-[2rem] border border-emerald-100 bg-[#f7fbf8] p-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เธเธนเนเธเธญเธ</p>
                            <div class="mt-5 flex items-center gap-5">
                                <div class="flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-white text-3xl text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="text-3xl font-black tracking-tight text-slate-950">{{ $selectedBookingDetails->user->full_name }}</h4>
                                    <p class="mt-2 text-sm font-bold text-slate-500">{{ $selectedBookingDetails->user->identity_label }} ยท {{ $selectedBookingDetails->user->identity_value }}</p>
                                </div>
                            </div>
                        </section>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เน€เธเธญเธฃเนเนเธ—เธฃเธจเธฑเธเธ—เน</p>
                                <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedBookingDetails->user->phone_number ?? '-' }}</p>
                            </div>
                            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เธเธ“เธฐ / เธซเธเนเธงเธขเธเธฒเธ</p>
                                <p class="mt-3 text-lg font-black text-slate-900">{{ $selectedBookingDetails->user->department ?? '-' }}</p>
                            </div>
                        </div>

                        <section class="overflow-hidden rounded-[2.25rem] border border-emerald-200 bg-emerald-600 text-white">
                            <div class="p-7">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/70">เธงเธฑเธเนเธฅเธฐเน€เธงเธฅเธฒ</p>
                                <div class="mt-5 flex items-center gap-5">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white">
                                        <i class="fa-regular fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-black">{{ $selectedBookingDetails->slot ? $selectedBookingDetails->slot->date->format('d F Y') : 'N/A' }}</p>
                                        <p class="mt-1 text-sm font-bold text-white/85">{{ $selectedBookingDetails->slot ? substr($selectedBookingDetails->slot->start_time, 0, 5) . ' - ' . substr($selectedBookingDetails->slot->end_time, 0, 5) : '-' }} เธ.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[2rem] border border-slate-200 bg-white p-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">เนเธเธกเน€เธเธ</p>
                            <div class="mt-4 flex items-center gap-4 rounded-[1.75rem] bg-[#f7fbf8] p-5">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-700">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <p class="text-base font-black text-slate-900">{{ $selectedBookingDetails->campaign->title }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Status ยท {{ $statusMeta[$selectedBookingDetails->status]['label'] ?? $selectedBookingDetails->status }}</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-[#f7fbf8] px-8 py-6">
                    <div class="flex flex-wrap gap-3">
                        @if($canManageBookings)
                            <a href="{{ route('staff.scan.campaign', $selectedBookingDetails->campaign->id) }}" target="_blank" class="inline-flex h-14 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 text-xs font-black uppercase tracking-[0.18em] text-emerald-700 transition-all hover:bg-emerald-50">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Open Scanner</span>
                            </a>
                        @endif
                        @if($canManageBookings && $selectedBookingDetails->status === 'pending')
                            <button wire:click="approve({{ $selectedBookingDetails->id }})" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">เธขเธทเธเธขเธฑเธเธเธฒเธฃเธเธญเธ</button>
                            <button wire:click="cancel({{ $selectedBookingDetails->id }})" class="flex-1 rounded-2xl border border-rose-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-rose-700 transition-all hover:bg-rose-50">เธขเธเน€เธฅเธดเธ</button>
                        @else
                            <button wire:click="closeDrawer" class="w-full rounded-2xl bg-slate-950 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-slate-800">เธเธดเธ”เธซเธเนเธฒเธ•เนเธฒเธ</button>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
