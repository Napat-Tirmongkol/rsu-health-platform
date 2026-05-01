<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-[2rem] border border-rose-200 bg-rose-50 px-6 py-4 text-sm font-bold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <section class="rounded-[2.75rem] border border-emerald-100 bg-white p-7 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="inline-flex items-center gap-3 rounded-full bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Clinic Campaigns
                </div>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">จัดการแคมเปญและกิจกรรม</h2>
                <p class="mt-3 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                    ดูภาพรวมโควตา สถานะการเปิดจอง และลิงก์สแกนประจำแคมเปญในหน้าเดียว เพื่อให้ทีมคลินิกวางแผนงานบริการได้ต่อเนื่อง
                </p>
            </div>

            <div class="flex w-full flex-col gap-4 xl:max-w-xl xl:items-end">
                <div class="relative w-full">
                    <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input
                        wire:model.live="search"
                        type="text"
                        placeholder="ค้นหาชื่อแคมเปญหรือกิจกรรม"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                    >
                </div>
                <button
                    wire:click="openAddModal"
                    class="inline-flex items-center gap-3 rounded-2xl bg-emerald-600 px-6 py-3.5 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>สร้างแคมเปญใหม่</span>
                </button>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2.75rem] border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-7 py-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Campaign Directory</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">รายการแคมเปญทั้งหมด</h3>
                </div>
                <p class="text-sm font-bold text-slate-500">
                    หน้า {{ $campaigns->lastPage() > 0 ? $campaigns->currentPage() : 0 }} / {{ $campaigns->lastPage() }} · รวม {{ number_format($campaigns->total()) }} รายการ
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f7fbf8] text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-7 py-5 lg:px-8">แคมเปญ</th>
                        <th class="px-6 py-5 text-center">โควตา</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-center">สิ้นสุดการจอง</th>
                        <th class="px-7 py-5 text-right lg:px-8">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($campaigns as $camp)
                        @php
                            $bookingCount = $camp->bookings_count ?? $camp->bookings()->count();
                            $percent = $camp->total_capacity > 0 ? min(100, ($bookingCount / $camp->total_capacity) * 100) : 0;
                        @endphp
                        <tr class="transition-colors hover:bg-[#fbfefc]">
                            <td class="px-7 py-6 lg:px-8">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                        <i class="fa-solid {{ $camp->type === 'vaccine' ? 'fa-syringe' : ($camp->type === 'training' ? 'fa-chalkboard-user' : 'fa-stethoscope') }} text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="truncate text-base font-black text-slate-950">{{ $camp->title }}</h4>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                                                {{ $camp->type }}
                                            </span>
                                            <a
                                                href="{{ route('staff.scan.campaign', $camp->id) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700 transition-all hover:bg-emerald-100"
                                            >
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>Open Scanner</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <div class="mx-auto max-w-[8rem]">
                                    <p class="text-sm font-black text-slate-900">{{ number_format($bookingCount) }} / {{ number_format($camp->total_capacity) }}</p>
                                    <div class="mt-3 h-2 rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] {{ $camp->status === 'active' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-500' }}">
                                    <span class="h-2 w-2 rounded-full {{ $camp->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $camp->status === 'active' ? 'เปิดรับจอง' : 'ปิดชั่วคราว' }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="text-sm font-black text-slate-700">{{ $camp->ends_at ? $camp->ends_at->format('d M Y') : '-' }}</span>
                            </td>
                            <td class="px-7 py-6 text-right lg:px-8">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        wire:click="generateNewToken({{ $camp->id }})"
                                        title="รีเซ็ตลิงก์แชร์"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition-all hover:bg-sky-100"
                                    >
                                        <i class="fa-solid fa-link"></i>
                                    </button>
                                    <button
                                        wire:click="edit({{ $camp->id }})"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-amber-700 transition-all hover:bg-amber-100"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button
                                        wire:click="delete({{ $camp->id }})"
                                        wire:confirm="ต้องการลบแคมเปญนี้ใช่หรือไม่?"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition-all hover:bg-rose-100"
                                    >
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-7 py-24 text-center lg:px-8">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                                    <i class="fa-solid fa-bullhorn text-3xl"></i>
                                </div>
                                <p class="mt-5 text-sm font-bold uppercase tracking-[0.22em] text-slate-400">ยังไม่มีแคมเปญในระบบ</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="border-t border-slate-100 bg-[#f7fbf8] px-7 py-5 lg:px-8">
                {{ $campaigns->links() }}
            </div>
        @endif
    </section>

    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>

            <div class="relative w-full max-w-3xl rounded-[2.75rem] border border-emerald-100 bg-white p-8 shadow-[0_24px_80px_rgba(15,23,42,0.18)] lg:p-10">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Campaign Form</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $editingId ? 'แก้ไขแคมเปญ' : 'สร้างแคมเปญใหม่' }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">กำหนดรายละเอียด ช่วงเวลาเปิดจอง และเงื่อนไขการอนุมัติให้พร้อมใช้งาน</p>
                    </div>
                    <button
                        wire:click="$set('showModal', false)"
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-7 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">ชื่อแคมเปญ</label>
                            <input wire:model="title" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                            @error('title') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">ประเภท</label>
                            <select wire:model="type" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                                <option value="vaccine">ฉีดวัคซีน</option>
                                <option value="training">อบรม / สัมมนา</option>
                                <option value="health_check">ตรวจสุขภาพ</option>
                                <option value="other">กิจกรรมอื่น ๆ</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">โควตา (คน)</label>
                            <input wire:model="total_capacity" type="number" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เริ่มเปิดจอง</label>
                            <input wire:model="starts_at" type="datetime-local" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">สิ้นสุดการจอง</label>
                            <input wire:model="ends_at" type="datetime-local" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-emerald-100 bg-[#f7fbf8] p-5">
                        <div class="flex items-center justify-between gap-5">
                            <div>
                                <h4 class="text-sm font-black text-slate-900">อนุมัติการจองอัตโนมัติ</h4>
                                <p class="mt-1 text-sm font-bold text-slate-500">เปิดใช้เมื่อต้องการให้ระบบยืนยันการจองทันทีโดยไม่ต้องรอแอดมิน</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input wire:model="is_auto_approve" type="checkbox" class="peer sr-only">
                                <div class="h-8 w-14 rounded-full bg-slate-200 transition-all after:absolute after:start-[4px] after:top-[4px] after:h-6 after:w-6 after:rounded-full after:bg-white after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-6"></div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">รายละเอียดแคมเปญ</label>
                        <textarea wire:model="description" rows="5" placeholder="อธิบายสิทธิ์การจอง เงื่อนไข เอกสารที่ต้องเตรียม หรือข้อปฏิบัติก่อนเข้ารับบริการ" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"></textarea>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                        <button type="submit" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">
                            {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างแคมเปญ' }}
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-slate-500 transition-all hover:bg-slate-50">
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
