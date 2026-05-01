<div class="space-y-6">

    {{-- Flash --}}
    @if (session()->has('clinic_msg'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('clinic_msg') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input
                    wire:model.live="search"
                    type="text"
                    placeholder="ค้นหาชื่อ, slug, code..."
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50 sm:w-72"
                >
            </div>
            <select wire:model.live="filterStatus" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                <option value="">ทุกสถานะ</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:-translate-y-0.5 hover:bg-sky-700"
        >
            <i class="fa-solid fa-plus"></i>
            เพิ่มคลินิก
        </button>
    </div>

    {{-- Pagination info --}}
    <p class="text-sm font-bold text-slate-500">
        หน้า {{ $clinics->lastPage() > 0 ? $clinics->currentPage() : 0 }} / {{ $clinics->lastPage() }} · รวม {{ number_format($clinics->total()) }} รายการ
    </p>

    {{-- Table --}}
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-4">Clinic</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Domain</th>
                        <th class="px-6 py-4 text-center">Users</th>
                        <th class="px-6 py-4 text-center">Staff</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clinics as $clinic)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($clinic->logo_url)
                                        <img src="{{ $clinic->logo_url }}" alt="" class="h-8 w-8 rounded-xl object-cover">
                                    @else
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl text-xs font-black text-white" style="background-color: {{ $clinic->primary_color ?? '#0ea5e9' }};">
                                            {{ mb_strtoupper(mb_substr($clinic->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-black text-slate-900">{{ $clinic->name }}</div>
                                        <div class="text-xs font-bold text-slate-400">{{ $clinic->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm font-bold text-slate-600">{{ $clinic->code ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-600">{{ $clinic->domain ?: '—' }}</td>
                            <td class="px-6 py-4 text-center font-black text-slate-900">{{ number_format($clinic->users_count) }}</td>
                            <td class="px-6 py-4 text-center font-black text-slate-900">{{ number_format($clinic->staff_count) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="h-4 w-4 rounded-full border border-white shadow-sm" style="background-color: {{ $clinic->primary_color ?? '#0ea5e9' }};"></div>
                                    <span class="font-mono text-xs font-bold text-slate-500">{{ $clinic->primary_color ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wide {{ $clinic->status === 'active' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-500' }}">
                                    {{ $clinic->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Impersonate --}}
                                    <form method="POST" action="{{ route('portal.impersonate', $clinic) }}">
                                        @csrf
                                        <button type="submit" title="Impersonate clinic" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-black uppercase tracking-wide text-amber-700 transition-all hover:bg-amber-100">
                                            <i class="fa-solid fa-mask"></i>
                                        </button>
                                    </form>
                                    <button wire:click="openEdit({{ $clinic->id }})" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] font-black uppercase tracking-wide text-sky-700 transition-all hover:bg-sky-100">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $clinic->id }})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-black uppercase tracking-wide text-red-600 transition-all hover:bg-red-100">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มีคลินิกในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clinics->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $clinics->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:ignore.self>
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl">
                <h3 class="text-xl font-black tracking-tight text-slate-900">
                    {{ $editingId ? 'แก้ไขคลินิก' : 'เพิ่มคลินิกใหม่' }}
                </h3>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">ชื่อคลินิก *</label>
                        <input wire:model="name" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="RSU Medical Clinic">
                        @error('name') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Slug * (subdomain)</label>
                            <input wire:model="slug" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="medical">
                            @error('slug') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Code *</label>
                            <input wire:model="code" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="RSU01">
                            @error('code') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Domain (optional)</label>
                        <input wire:model="domain" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="medical.rsu.ac.th">
                        @error('domain') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Status *</label>
                        <select wire:model="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Branding & Contact</p>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">คำอธิบายคลินิก</label>
                                <textarea wire:model="description" rows="2" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="คำอธิบายสั้นๆ เกี่ยวกับคลินิก..."></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Logo URL</label>
                                    <input wire:model="logoUrl" type="url" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="https://...">
                                    @error('logoUrl') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Primary Color</label>
                                    <div class="flex gap-2">
                                        <input wire:model="primaryColor" type="color" class="h-[46px] w-12 cursor-pointer rounded-xl border border-slate-200 p-1">
                                        <input wire:model="primaryColor" type="text" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="#0ea5e9">
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Contact Email</label>
                                    <input wire:model="contactEmail" type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="clinic@rsu.ac.th">
                                    @error('contactEmail') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Contact Phone</label>
                                    <input wire:model="contactPhone" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="02-xxx-xxxx">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 transition-all hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="save" class="rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:bg-sky-700">
                        {{ $editingId ? 'บันทึกการแก้ไข' : 'เพิ่มคลินิก' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-sm rounded-[2rem] bg-white p-8 shadow-2xl">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50">
                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-500"></i>
                </div>
                <h3 class="mt-4 text-xl font-black text-slate-900">ยืนยันการลบ</h3>
                <p class="mt-2 text-sm font-bold text-slate-500">การลบคลินิกจะส่งผลต่อข้อมูลทั้งหมดที่เชื่อมโยง ดำเนินการต่อหรือไม่?</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 transition-all hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="delete" class="rounded-2xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-100 transition-all hover:bg-red-700">
                        ยืนยันลบ
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
