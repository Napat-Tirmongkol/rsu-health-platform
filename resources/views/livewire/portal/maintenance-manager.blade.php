<div class="space-y-8">

    {{-- Flash --}}
    @if (session()->has('maint_msg'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('maint_msg') }}
        </div>
    @endif

    {{-- Maintenance Mode Toggle --}}
    <section class="rounded-[2rem] border {{ $maintenanceMode ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-white' }} p-6 shadow-sm transition-colors">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">System Status</p>
                <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">Maintenance Mode</h3>
                <p class="mt-1 text-sm font-bold text-slate-500">
                    สถานะปัจจุบัน:
                    @if ($maintenanceMode)
                        <span class="font-black text-red-600">เปิดอยู่ — ผู้ใช้จะเห็นหน้า Maintenance</span>
                    @else
                        <span class="font-black text-emerald-600">ปิดอยู่ — ระบบทำงานปกติ</span>
                    @endif
                </p>
            </div>
            <button
                wire:click="toggleMaintenance"
                class="inline-flex items-center gap-2 rounded-2xl px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:-translate-y-0.5 {{ $maintenanceMode ? 'bg-emerald-600 shadow-emerald-100 hover:bg-emerald-700' : 'bg-red-600 shadow-red-100 hover:bg-red-700' }}"
            >
                <i class="fa-solid {{ $maintenanceMode ? 'fa-check' : 'fa-power-off' }}"></i>
                {{ $maintenanceMode ? 'ปิด Maintenance Mode' : 'เปิด Maintenance Mode' }}
            </button>
        </div>

        <div class="mt-5 flex gap-3">
            <input
                wire:model="maintenanceMessage"
                type="text"
                placeholder="ข้อความแจ้งผู้ใช้ระหว่าง Maintenance (optional)..."
                class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50"
            >
            <button wire:click="saveMessage" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition-all hover:bg-slate-50">
                บันทึก
            </button>
        </div>
    </section>

    {{-- Announcements --}}
    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Announcements</p>
                <h3 class="mt-1 text-xl font-black tracking-tight text-slate-900">ประกาศระบบ</h3>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="filterClinic" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                    <option value="">ทุกคลินิก</option>
                    @foreach ($clinics as $clinic)
                        <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                    @endforeach
                </select>
                <button
                    wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:-translate-y-0.5 hover:bg-sky-700"
                >
                    <i class="fa-solid fa-plus"></i>
                    เพิ่มประกาศ
                </button>
            </div>
        </div>

        {{-- Pagination info --}}
        <p class="text-sm font-bold text-slate-500">
            หน้า {{ $announcements->lastPage() > 0 ? $announcements->currentPage() : 0 }} / {{ $announcements->lastPage() }} · รวม {{ number_format($announcements->total()) }} รายการ
        </p>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                            <th class="px-6 py-4">Clinic</th>
                            <th class="px-6 py-4">Title</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4 text-center">Active</th>
                            <th class="px-6 py-4">ช่วงเวลา</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($announcements as $ann)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <span class="rounded-xl border border-sky-100 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase text-sky-700">
                                        {{ $ann->clinic?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="max-w-[18rem] px-6 py-4">
                                    <div class="font-black text-slate-800">{{ $ann->title }}</div>
                                    @if($ann->content)
                                        <div class="mt-0.5 text-xs font-bold text-slate-400">{{ \Illuminate\Support\Str::limit($ann->content, 60) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php $typeColors = ['info' => 'border-blue-200 bg-blue-50 text-blue-700', 'warning' => 'border-amber-200 bg-amber-50 text-amber-700', 'danger' => 'border-red-200 bg-red-50 text-red-700']; @endphp
                                    <span class="rounded-xl border px-3 py-1 text-[10px] font-black uppercase {{ $typeColors[$ann->type] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $ann->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm {{ $ann->is_active ? 'text-emerald-600' : 'text-slate-300' }}">
                                        <i class="fa-solid {{ $ann->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-500">
                                    @if($ann->starts_at || $ann->ends_at)
                                        {{ $ann->starts_at?->format('d/m/y') ?? '∞' }} – {{ $ann->ends_at?->format('d/m/y') ?? '∞' }}
                                    @else
                                        <span class="text-slate-400">ไม่กำหนด</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button wire:click="openEdit({{ $ann->id }})" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] font-black text-sky-700 transition-all hover:bg-sky-100">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $ann->id }})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-black text-red-600 transition-all hover:bg-red-100">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มีประกาศ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($announcements->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $announcements->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- Create/Edit Announcement Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl">
                <h3 class="text-xl font-black tracking-tight text-slate-900">
                    {{ $editingId ? 'แก้ไขประกาศ' : 'เพิ่มประกาศใหม่' }}
                </h3>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Clinic *</label>
                        <select wire:model="announcementClinicId" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                            @foreach ($clinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Title *</label>
                        <input wire:model="title" type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="หัวข้อประกาศ">
                        @error('title') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Content</label>
                        <textarea wire:model="content" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50" placeholder="รายละเอียด..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">Type *</label>
                            <select wire:model="type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex cursor-pointer items-center gap-3">
                                <input wire:model="isActive" type="checkbox" class="h-5 w-5 rounded-lg border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span class="text-sm font-black text-slate-800">เปิดใช้งาน</span>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">เริ่มต้น</label>
                            <input wire:model="startsAt" type="datetime-local" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-black uppercase tracking-widest text-slate-500">สิ้นสุด</label>
                            <input wire:model="endsAt" type="datetime-local" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-50">
                            @error('endsAt') <p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 transition-all hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="save" class="rounded-2xl bg-sky-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-sky-100 transition-all hover:bg-sky-700">
                        {{ $editingId ? 'บันทึก' : 'เพิ่มประกาศ' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-sm rounded-[2rem] bg-white p-8 shadow-2xl">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50">
                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-500"></i>
                </div>
                <h3 class="mt-4 text-xl font-black text-slate-900">ยืนยันการลบประกาศ</h3>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-600 hover:bg-slate-50">ยกเลิก</button>
                    <button wire:click="delete" class="rounded-2xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-100 hover:bg-red-700">ยืนยันลบ</button>
                </div>
            </div>
        </div>
    @endif

</div>
