<div class="space-y-6">

    {{-- Flash --}}
    @if (session()->has('settings_msg'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('settings_msg') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input
                wire:model.live="search"
                type="text"
                placeholder="ค้นหา key..."
                class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50 sm:w-72"
            >
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:-translate-y-0.5 hover:bg-sky-700"
        >
            <i class="fa-solid fa-plus"></i>
            เพิ่ม Global Setting
        </button>
    </div>

    {{-- Pagination info --}}
    <p class="text-sm font-bold text-slate-500">
        หน้า {{ $settings->lastPage() > 0 ? $settings->currentPage() : 0 }} / {{ $settings->lastPage() }} · รวม {{ number_format($settings->total()) }} รายการ
    </p>

    {{-- Table --}}
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-4">Key</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Value</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($settings as $setting)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-4 font-mono text-sm font-black text-slate-800">{{ $setting->key }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-600">
                                    {{ $setting->type ?? 'string' }}
                                </span>
                            </td>
                            <td class="max-w-xs px-6 py-4 text-sm font-bold text-slate-500">
                                {{ \Illuminate\Support\Str::limit((string) $setting->value, 80) ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="openEdit({{ $setting->id }})" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] font-black text-sky-700 transition-all hover:bg-sky-100">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $setting->id }})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-black text-red-600 transition-all hover:bg-red-100">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มี global settings ในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($settings->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $settings->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl">
                <h3 class="text-xl font-black tracking-tight text-slate-900">
                    {{ $editingId ? 'แก้ไข Global Setting' : 'เพิ่ม Global Setting' }}
                </h3>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Key *</label>
                        <input wire:model="key" type="text" {{ $editingId ? 'disabled' : '' }} class="w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50 disabled:bg-slate-50" placeholder="site_name">
                        @error('key') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Type *</label>
                        <select wire:model="type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                            <option value="string">string</option>
                            <option value="boolean">boolean</option>
                            <option value="integer">integer</option>
                            <option value="json">json</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Value</label>
                        <textarea wire:model="value" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="ค่าของ setting..."></textarea>
                        @error('value') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 transition-all hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="save" class="rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:bg-sky-700">
                        {{ $editingId ? 'บันทึก' : 'เพิ่ม' }}
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
                <p class="mt-2 text-sm font-bold text-slate-500">การลบ global setting อาจส่งผลต่อการทำงานของระบบ</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 hover:bg-slate-50">ยกเลิก</button>
                    <button wire:click="delete" class="rounded-2xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-100 hover:bg-red-700">ยืนยันลบ</button>
                </div>
            </div>
        </div>
    @endif

</div>
