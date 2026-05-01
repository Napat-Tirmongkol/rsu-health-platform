<div class="space-y-8">

    {{-- Header --}}
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">จัดการ Admin</h1>
            <p class="mt-1 text-sm font-bold uppercase tracking-widest text-slate-400">ครอบคลุมทุก clinic · Portal level access</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-3 rounded-2xl px-7 py-3.5 text-sm font-black text-white shadow-lg transition-all hover:-translate-y-0.5"
            style="background:#7c3aed; box-shadow: 0 10px 25px rgba(124,58,237,0.25);"
        >
            <i class="fa-solid fa-user-plus"></i>
            <span>เพิ่ม Admin</span>
        </button>
    </div>

    {{-- Flash message --}}
    @if (session()->has('portal_admin_message'))
        <div class="flex items-center gap-3 rounded-2xl border border-violet-100 bg-violet-50 p-4 text-sm font-bold text-violet-700">
            <i class="fa-solid fa-circle-check text-lg"></i>
            {{ session('portal_admin_message') }}
        </div>
    @endif

    {{-- Search + filter --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1 group">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm transition-colors group-focus-within:text-violet-500"></i>
            <input
                wire:model.live="search"
                type="text"
                placeholder="ค้นหาชื่อหรืออีเมล..."
                class="w-full rounded-2xl border border-slate-100 bg-white py-3.5 pl-11 pr-6 text-sm font-bold text-slate-700 shadow-sm transition-all focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-50"
            >
        </div>
        <select
            wire:model.live="filterClinic"
            class="rounded-2xl border border-slate-100 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-50 sm:w-56"
        >
            <option value="">ทุก clinic</option>
            @foreach ($clinics as $clinic)
                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Admin</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Clinic</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Google ID</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Workspace Access</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Default</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($admins as $admin)
                        <tr class="align-top hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-black text-white" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
                                        {{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">{{ $admin->name }}</p>
                                        <p class="mt-0.5 text-xs font-bold text-slate-400">{{ $admin->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700">
                                    <i class="fa-solid fa-hospital text-[9px]"></i>
                                    {{ $admin->clinic?->name ?? 'clinic #' . $admin->clinic_id }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-xs font-bold text-slate-500">
                                {{ $admin->google_id ? mb_strimwidth($admin->google_id, 0, 20, '…') : '—' }}
                            </td>
                            <td class="px-6 py-5 text-xs font-bold text-slate-700">{{ $this->moduleSummary($admin) }}</td>
                            <td class="px-6 py-5 text-xs font-bold text-slate-500">
                                {{ config("admin_modules.modules.{$admin->default_workspace}.label", 'Platform Home') }}
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="openEditModal({{ $admin->id }})"
                                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black uppercase tracking-[0.16em] transition-all hover:opacity-80"
                                        style="background:#f3f0ff; color:#7c3aed;"
                                    >
                                        <i class="fa-solid fa-pen text-[10px]"></i> แก้ไข
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $admin->id }})"
                                        class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2.5 text-xs font-black uppercase tracking-[0.16em] text-rose-600 transition-all hover:bg-rose-100"
                                    >
                                        <i class="fa-solid fa-trash text-[10px]"></i> ลบ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                                    <i class="fa-solid fa-user-shield text-3xl"></i>
                                </div>
                                <p class="mt-5 text-sm font-bold uppercase tracking-[0.2em] text-slate-400">ไม่พบ Admin</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($admins->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                <div class="mb-3 text-xs font-bold text-slate-400">
                    หน้า {{ $admins->currentPage() }} / {{ $admins->lastPage() }} · รวม {{ number_format($admins->total()) }} รายการ
                </div>
                {{ $admins->links() }}
            </div>
        @endif
    </div>

    {{-- ── Create / Edit Modal ─────────────────────────────────────── --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-md">
            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-[2.5rem] bg-white shadow-2xl">

                <div class="sticky top-0 z-10 border-b border-slate-100 bg-white px-8 py-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-violet-500">Portal Administration</p>
                    <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">
                        {{ $editingId ? 'แก้ไข Admin' : 'เพิ่ม Admin ใหม่' }}
                    </h3>
                </div>

                <div class="space-y-6 px-8 py-8">

                    {{-- Basic info --}}
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2.5 ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">ชื่อ *</label>
                            <input wire:model="name" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-50">
                            @error('name') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2.5 ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">อีเมล *</label>
                            <input wire:model="email" type="email" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-50">
                            @error('email') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2.5 ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Google ID</label>
                            <input wire:model="googleId" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-50">
                            @error('googleId') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2.5 ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Clinic *</label>
                            <select wire:model="clinicId" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-50">
                                @foreach ($clinics as $clinic)
                                    <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                @endforeach
                            </select>
                            @error('clinicId') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Full platform toggle --}}
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                        <input wire:model.live="fullPlatformAccess" type="checkbox" class="mt-1 h-5 w-5 rounded border-slate-300 focus:ring-violet-500" style="color:#7c3aed;">
                        <div>
                            <p class="text-sm font-black text-slate-800">Full platform access</p>
                            <p class="mt-1 text-sm font-bold leading-relaxed text-slate-500">เข้าถึงทุก workspace และสิทธิ์การทำงานทุกอย่างโดยอัตโนมัติ</p>
                        </div>
                    </label>

                    {{-- Module permissions --}}
                    <div class="{{ $fullPlatformAccess ? 'opacity-40 pointer-events-none' : '' }}">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Workspace Permissions</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @foreach ($availableModules as $moduleKey => $module)
                                <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                                    <input wire:model.live="selectedModules" type="checkbox" value="{{ $moduleKey }}" class="mt-1 h-5 w-5 rounded border-slate-300 focus:ring-violet-500" style="color:#7c3aed;" @disabled($fullPlatformAccess)>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">{{ $module['label'] }}</p>
                                        <p class="mt-1 text-xs font-bold leading-relaxed text-slate-500">{{ $module['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedModules') <p class="mt-3 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Action permissions --}}
                    <div class="{{ $fullPlatformAccess ? 'opacity-40 pointer-events-none' : '' }}">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Action Permissions</p>
                        <div class="mt-4 space-y-4">
                            @foreach ($availableActions as $moduleKey => $group)
                                @if ($fullPlatformAccess || in_array($moduleKey, $selectedModules, true))
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5">
                                        <p class="text-sm font-black text-slate-700">{{ $group['label'] }}</p>
                                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                                            @foreach ($group['actions'] as $action)
                                                <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                                                    <input wire:model="selectedActions" type="checkbox" value="{{ $action['key'] }}" class="mt-1 h-5 w-5 rounded border-slate-300 focus:ring-violet-500" style="color:#7c3aed;" @disabled($fullPlatformAccess)>
                                                    <div>
                                                        <p class="text-sm font-black text-slate-800">{{ $action['label'] }}</p>
                                                        <p class="mt-1 text-xs font-bold leading-relaxed text-slate-500">{{ $action['description'] }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('selectedActions') <p class="mt-3 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Default workspace --}}
                    <div>
                        <label class="mb-2.5 ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Default Workspace</label>
                        <select wire:model="defaultWorkspace" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 transition-all focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-50">
                            @foreach ($availableModules as $moduleKey => $module)
                                @if ($fullPlatformAccess || in_array($moduleKey, $selectedModules, true))
                                    <option value="{{ $moduleKey }}">{{ $module['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('defaultWorkspace') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>

                <div class="flex items-center gap-4 border-t border-slate-100 px-8 py-6">
                    <button wire:click="$set('showModal', false)" type="button" class="flex-1 rounded-2xl bg-slate-100 py-4 text-sm font-black text-slate-500 transition-all hover:bg-slate-200 active:scale-95">
                        ยกเลิก
                    </button>
                    <button wire:click="save" type="button" class="flex-[1.6] rounded-2xl py-4 text-sm font-black text-white shadow-xl transition-all active:scale-95" style="background:#7c3aed; box-shadow: 0 10px 25px rgba(124,58,237,0.3);">
                        บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Delete Confirm Modal ─────────────────────────────────────── --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-md">
            <div class="w-full max-w-md overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="px-8 py-8 text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50">
                        <i class="fa-solid fa-triangle-exclamation text-2xl text-rose-500"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">ยืนยันการลบ</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">Admin รายนี้จะถูกลบออกจากระบบถาวร ไม่สามารถกู้คืนได้</p>
                </div>
                <div class="flex gap-4 border-t border-slate-100 px-8 py-6">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="flex-1 rounded-2xl bg-slate-100 py-4 text-sm font-black text-slate-500 transition-all hover:bg-slate-200 active:scale-95">
                        ยกเลิก
                    </button>
                    <button wire:click="delete" type="button" class="flex-1 rounded-2xl bg-rose-600 py-4 text-sm font-black text-white transition-all hover:bg-rose-700 active:scale-95">
                        ลบ Admin
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
