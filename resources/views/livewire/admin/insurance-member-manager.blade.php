<div class="space-y-6">

    {{-- Alert --}}
    @if($importResult)
        <div class="flex items-center gap-3 rounded-2xl px-5 py-4 text-sm font-bold
            {{ $importResult['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
            <i class="fa-solid {{ $importResult['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
            <span>{{ $importResult['message'] }}</span>
            <button wire:click="$set('importResult', null)" class="ml-auto opacity-60 hover:opacity-100">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">ทั้งหมด</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600">Active</p>
            <p class="mt-2 text-3xl font-black text-emerald-700">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">มีกรมธรรม์</p>
            <p class="mt-2 text-3xl font-black text-blue-700">{{ number_format($stats['covered']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">รอกรมธรรม์</p>
            <p class="mt-2 text-3xl font-black text-amber-700">{{ number_format($stats['pending']) }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative min-w-[220px] flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="ค้นหารหัส / ชื่อ / เลขบัตร..."
                class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>

        <select wire:model.live="filterType"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 focus:border-emerald-400 focus:outline-none">
            <option value="">ทุกประเภท</option>
            <option value="student">นักศึกษา</option>
            <option value="staff">บุคลากร</option>
        </select>

        <select wire:model.live="filterMemberStatus"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 focus:border-emerald-400 focus:outline-none">
            <option value="">สถานะสมาชิก</option>
            <option value="active">Active</option>
            <option value="resigned">ออกจากงาน</option>
            <option value="inactive">Inactive</option>
        </select>

        <select wire:model.live="filterInsuranceStatus"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 focus:border-emerald-400 focus:outline-none">
            <option value="">สถานะประกัน</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
        </select>

        <div class="ml-auto flex items-center gap-2">
            <button wire:click="$set('showImportMemberModal', true)"
                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span>นำเข้าสมาชิก</span>
            </button>
            <button wire:click="$set('showImportPolicyModal', true)"
                class="inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100">
                <i class="fa-solid fa-file-import"></i>
                <span>นำเข้ากรมธรรม์</span>
            </button>
            <a href="{{ route('admin.insurance.export') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-xs font-black uppercase tracking-widest text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fa-solid fa-file-arrow-down"></i>
                <span>Export ส่งประกัน</span>
            </a>
        </div>
    </div>

    {{-- Pagination info --}}
    <div class="flex items-center justify-between text-[11px] font-bold text-slate-400">
        <span>หน้า {{ $members->currentPage() }} / {{ $members->lastPage() }} · รวม {{ number_format($members->total()) }} รายการ</span>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">รหัส</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">ชื่อ-นามสกุล</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">ประเภท</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">สถานะสมาชิก</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">เลขกรมธรรม์</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">สถานะประกัน</th>
                    <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">คุ้มครองถึง</th>
                    <th class="px-5 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($members as $member)
                    <tr class="transition hover:bg-slate-50/60">
                        <td class="px-5 py-4 font-mono text-xs font-bold text-slate-600">{{ $member->member_id }}</td>
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-800">{{ $member->full_name ?: '-' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $member->department }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                {{ $member->member_type === 'staff' ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ $member->member_type === 'staff' ? 'บุคลากร' : 'นักศึกษา' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                {{ $member->member_status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($member->member_status === 'resigned' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ $member->member_status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $member->policy_number ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                {{ $member->insurance_status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($member->insurance_status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ $member->insurance_status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $member->expires_at ? $member->expires_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button wire:click="openDetail({{ $member->id }})"
                                class="rounded-xl border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:bg-slate-50">
                                ดูข้อมูล
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-sm font-bold text-slate-400">
                            <i class="fa-solid fa-shield-xmark mb-3 block text-3xl opacity-30"></i>
                            ไม่พบข้อมูลสมาชิกประกัน
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($members->hasPages())
        <div class="flex items-center justify-center gap-1">
            {{-- First --}}
            @if($members->onFirstPage())
                <span class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-300">«</span>
            @else
                <button wire:click="gotoPage(1)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">«</button>
            @endif

            {{-- Prev --}}
            @if($members->onFirstPage())
                <span class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-300">‹</span>
            @else
                <button wire:click="previousPage" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">‹</button>
            @endif

            {{-- Pages --}}
            @foreach(range(max(1, $members->currentPage() - 2), min($members->lastPage(), $members->currentPage() + 2)) as $page)
                @if($page === $members->currentPage())
                    <span class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white">{{ $page }}</span>
                @else
                    <button wire:click="gotoPage({{ $page }})" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">{{ $page }}</button>
                @endif
            @endforeach

            {{-- Next --}}
            @if($members->hasMorePages())
                <button wire:click="nextPage" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">›</button>
            @else
                <span class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-300">›</span>
            @endif

            {{-- Last --}}
            @if($members->hasMorePages())
                <button wire:click="gotoPage({{ $members->lastPage() }})" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">»</button>
            @else
                <span class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-300">»</span>
            @endif
        </div>
    @endif

    {{-- Import Member Modal --}}
    @if($showImportMemberModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showImportMemberModal', false)"></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
                <h3 class="mb-1 text-xl font-black text-slate-900">นำเข้าข้อมูลสมาชิก</h3>
                <p class="mb-6 text-sm text-slate-500">อัพโหลดไฟล์ Excel จากฝ่ายทะเบียน</p>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">ประเภทสมาชิก</label>
                        <select wire:model="importMemberType"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-emerald-400 focus:outline-none">
                            <option value="student">นักศึกษา</option>
                            <option value="staff">บุคลากร</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">สถานะ</label>
                        <select wire:model="importMemberStatus"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 focus:border-emerald-400 focus:outline-none">
                            <option value="active">Active (ปัจจุบัน)</option>
                            <option value="resigned">ออกจากงาน / พ้นสภาพ</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">ไฟล์ Excel</label>
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4">
                            <input wire:model="memberFile" type="file" accept=".xlsx,.xls,.csv"
                                class="w-full text-sm text-slate-600 file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-widest file:text-white hover:file:bg-emerald-700">
                        </div>
                        <p class="mt-1.5 text-[10px] text-slate-400">Columns: รหัส, ชื่อ, นามสกุล, เลขบัตรประชาชน, คณะ/แผนก</p>
                    </div>

                    @error('memberFile')
                        <p class="text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showImportMemberModal', false)"
                        class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="importMembers" wire:loading.attr="disabled"
                        class="flex-1 rounded-2xl bg-emerald-600 py-3 text-sm font-black uppercase tracking-widest text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="importMembers">นำเข้า</span>
                        <span wire:loading wire:target="importMembers">กำลังนำเข้า...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Policy Modal --}}
    @if($showImportPolicyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showImportPolicyModal', false)"></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
                <h3 class="mb-1 text-xl font-black text-slate-900">นำเข้าเลขกรมธรรม์</h3>
                <p class="mb-6 text-sm text-slate-500">อัพโหลดไฟล์ที่ได้รับกลับจากเมืองไทยประกันภัย</p>

                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">ไฟล์ Excel จากประกัน</label>
                    <div class="rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50 p-4">
                        <input wire:model="policyFile" type="file" accept=".xlsx,.xls,.csv"
                            class="w-full text-sm text-slate-600 file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-widest file:text-white hover:file:bg-blue-700">
                    </div>
                    <p class="mt-1.5 text-[10px] text-slate-400">Columns: รหัส, เลขกรมธรรม์, วันเริ่มคุ้มครอง, วันสิ้นสุดคุ้มครอง</p>
                </div>

                @error('policyFile')
                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showImportPolicyModal', false)"
                        class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                        ยกเลิก
                    </button>
                    <button wire:click="importPolicies" wire:loading.attr="disabled"
                        class="flex-1 rounded-2xl bg-blue-600 py-3 text-sm font-black uppercase tracking-widest text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="importPolicies">นำเข้า</span>
                        <span wire:loading wire:target="importPolicies">กำลังนำเข้า...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedMember)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeDetail"></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900">{{ $selectedMember->full_name ?: 'ไม่ระบุชื่อ' }}</h3>
                        <p class="mt-1 font-mono text-sm text-slate-400">{{ $selectedMember->member_id }}</p>
                    </div>
                    <button wire:click="closeDetail" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">ประเภท</dt>
                        <dd class="font-bold text-slate-800">{{ $selectedMember->member_type === 'staff' ? 'บุคลากร' : 'นักศึกษา' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">เลขบัตรประชาชน</dt>
                        <dd class="font-mono font-bold text-slate-800">{{ $selectedMember->national_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">คณะ / แผนก</dt>
                        <dd class="font-bold text-slate-800">{{ $selectedMember->department ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">สถานะสมาชิก</dt>
                        <dd>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                {{ $selectedMember->member_status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $selectedMember->member_status }}
                            </span>
                        </dd>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <dt class="mb-1 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">เลขกรมธรรม์</dt>
                        <dd class="font-mono text-base font-black text-slate-900">{{ $selectedMember->policy_number ?? 'ยังไม่มีกรมธรรม์' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">สถานะประกัน</dt>
                        <dd>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                {{ $selectedMember->insurance_status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($selectedMember->insurance_status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ $selectedMember->insurance_status }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">เริ่มคุ้มครอง</dt>
                        <dd class="font-bold text-slate-800">{{ $selectedMember->coverage_start_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">สิ้นสุดคุ้มครอง</dt>
                        <dd class="font-bold text-slate-800">{{ $selectedMember->expires_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-bold text-slate-500">บริษัทประกัน</dt>
                        <dd class="font-bold text-slate-800">{{ $selectedMember->provider_name ?? 'เมืองไทยประกันภัย' }}</dd>
                    </div>
                </dl>

                <button wire:click="closeDetail"
                    class="mt-6 w-full rounded-2xl border border-slate-200 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    ปิด
                </button>
            </div>
        </div>
    @endif
</div>
