<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-slate-800">Admin Module Access</h2>
            <p class="mt-1 text-sm font-bold uppercase tracking-widest text-slate-400">Workspace permissions for platform admins</p>
        </div>

        <div class="relative group">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
            <input wire:model.live="search" type="text" placeholder="ค้นหาชื่อหรืออีเมลแอดมิน..." class="w-full rounded-2xl border border-slate-100 bg-white py-3.5 pl-11 pr-6 text-sm font-bold text-slate-700 shadow-sm transition-all focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-50 md:w-80">
        </div>
    </div>

    @if (session()->has('admin_access_message'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-600">
            <i class="fa-solid fa-circle-check text-lg"></i>
            {{ session('admin_access_message') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Admin</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Email</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Workspace Access</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($admins as $admin)
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-sm font-black text-slate-500 shadow-inner">
                                        {{ mb_strtoupper(mb_substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">{{ $admin->name }}</p>
                                        <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                            {{ $admin->id === auth('admin')->id() ? 'Current Session' : 'Platform Admin' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-bold text-slate-500">{{ $admin->email }}</td>
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-slate-700">{{ $this->moduleSummary($admin) }}</p>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <button wire:click="openAccessModal({{ $admin->id }})" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-slate-200 transition-all hover:-translate-y-0.5 hover:shadow-slate-300">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>Manage Access</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                                    <i class="fa-solid fa-user-lock text-3xl"></i>
                                </div>
                                <p class="mt-5 text-sm font-bold uppercase tracking-[0.2em] text-slate-400">ยังไม่มีข้อมูลแอดมินในระบบ</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($admins->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                <div class="mb-3 text-xs font-bold text-slate-400">
                    หน้า {{ $admins->currentPage() }} / {{ $admins->lastPage() }} · รวม {{ $admins->total() }} รายการ
                </div>
                {{ $admins->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-6 backdrop-blur-md">
            <div class="w-full max-w-2xl overflow-hidden rounded-[2.5rem] bg-white shadow-2xl">
                <div class="border-b border-slate-100 px-8 py-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Access Control</p>
                    <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">กำหนดสิทธิ์การเข้าถึง workspace</h3>
                </div>

                <div class="space-y-6 px-8 py-8">
                    <label class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                        <input wire:model.live="fullPlatformAccess" type="checkbox" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-black text-slate-800">Full platform access</p>
                            <p class="mt-1 text-sm font-bold leading-relaxed text-slate-500">มองเห็นและเข้าถึงทุก workspace รวมถึงโมดูลใหม่ที่เพิ่มเข้ามาในอนาคต</p>
                        </div>
                    </label>

                    <div class="{{ $fullPlatformAccess ? 'opacity-50' : '' }}">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Workspace Selection</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @foreach ($availableModules as $moduleKey => $module)
                                <label class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                                    <input wire:model="selectedModules" type="checkbox" value="{{ $moduleKey }}" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @disabled($fullPlatformAccess)>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">{{ $module['label'] }}</p>
                                        <p class="mt-1 text-sm font-bold leading-relaxed text-slate-500">{{ $module['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedModules') <p class="mt-3 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4 border-t border-slate-100 px-8 py-6">
                    <button wire:click="$set('showModal', false)" type="button" class="flex-1 rounded-2xl bg-slate-100 py-4 text-sm font-black text-slate-500 transition-all active:scale-95">ยกเลิก</button>
                    <button wire:click="saveAccess" type="button" class="flex-[1.6] rounded-2xl bg-slate-900 py-4 text-sm font-black text-white shadow-xl shadow-slate-200 transition-all active:scale-95">บันทึกสิทธิ์</button>
                </div>
            </div>
        </div>
    @endif
</div>
