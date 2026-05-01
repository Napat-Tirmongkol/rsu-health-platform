@php
    $adminUser = Auth::guard('admin')->user();
    $canProcessReturns = ! $adminUser || $adminUser->hasActionAccess('borrow.return.process');
@endphp
<div class="space-y-8 animate-in fade-in duration-700">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2.5rem] border border-amber-100 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            เธฃเธฐเธเธเธเธทเธเธญเธธเธเธเธฃเธ“เนเธขเธฑเธเนเธกเนเธเธฃเนเธญเธกเนเธเนเธเธฒเธเน€เธ•เนเธกเธฃเธนเธเนเธเธ เธเธฃเธธเธ“เธฒเธฃเธฑเธ `php artisan migrate` เน€เธเธทเนเธญเธชเธฃเนเธฒเธเธ•เธฒเธฃเธฒเธ borrow เธเนเธญเธ
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card icon="fa-box-open" badge="กำลังยืม" eyebrow="รอรับคืน" :value="number_format($stats['borrowed'])" description="จำนวนรายการที่ยังอยู่ระหว่างการยืมและรอการรับคืน" variant="info" />

        <x-admin.stat-card icon="fa-triangle-exclamation" badge="เกินกำหนด" eyebrow="ต้องติดตาม" :value="number_format($stats['overdue'])" description="รายการที่เลยกำหนดคืนแล้วและควรเร่งติดตามต่อจากหน้างาน" variant="danger" />

        <x-admin.stat-card icon="fa-file-invoice-dollar" badge="ค่าปรับค้าง" eyebrow="รอเคลียร์" :value="number_format($stats['fine_pending'])" description="จำนวนรายการที่มีค่าปรับค้างอยู่และต้องเคลียร์ก่อนปิดงาน" variant="warning" />

        <x-admin.stat-card icon="fa-rotate-left" badge="วันนี้" eyebrow="คืนแล้ววันนี้" :value="number_format($stats['returned_today'])" description="จำนวนอุปกรณ์ที่ถูกรับคืนแล้วในวันนี้ เพื่อดูความเคลื่อนไหวของรอบคืน" variant="soft-accent" />
    </div>

    <div class="flex flex-col gap-6 rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div class="flex w-full items-center gap-2 overflow-x-auto rounded-[1.5rem] bg-slate-50 p-1.5 lg:w-auto">
            <button wire:click="$set('filter', 'all')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">เธ—เธฑเนเธเธซเธกเธ”</button>
            <button wire:click="$set('filter', 'overdue')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'overdue' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">เน€เธเธดเธเธเธณเธซเธเธ”</button>
            <button wire:click="$set('filter', 'due_today')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'due_today' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">เธเธฃเธเธเธณเธซเธเธ”เธงเธฑเธเธเธตเน</button>
            <button wire:click="$set('filter', 'fine_pending')" class="rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === 'fine_pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400' }}">เธเนเธฒเธเธฃเธฑเธเธเนเธฒเธ</button>
        </div>

        <div class="relative w-full lg:max-w-md">
            <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="เธเนเธเธซเธฒเธเธนเนเธขเธทเธก เธญเธธเธเธเธฃเธ“เน เธซเธฃเธทเธญ serial..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
        </div>
    </div>

    <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @forelse($records as $record)
                @php
                    $overdueDays = $this->overdueDays($record);
                    $fineAmount = $this->calculatedFineAmount($record);
                @endphp
                <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-100 bg-slate-50/50 p-6 shadow-sm transition-all hover:shadow-md">
                    @if($overdueDays > 0)
                        <div class="absolute right-5 top-5 rounded-full bg-rose-500 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white">
                            เน€เธเธดเธ {{ $overdueDays }} เธงเธฑเธ
                        </div>
                    @endif

                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                            <i class="fa-solid fa-box text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-lg font-black text-slate-900">{{ $record->item?->name ?? $record->category?->name ?? 'Unknown item' }}</h4>
                            <p class="mt-1 text-xs font-black uppercase tracking-widest text-slate-400">
                                S/N: {{ $record->item?->serial_number ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-slate-100 pt-4 text-sm font-bold">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-400">เธเธนเนเธขเธทเธก</span>
                            <span class="text-right text-slate-800">{{ $record->borrower?->full_name ?: $record->borrower?->name ?: 'Unknown user' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-400">Identity</span>
                            <span class="text-right text-slate-700">{{ $record->borrower?->identity_value ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-400">เธงเธฑเธเธ—เธตเนเธขเธทเธก</span>
                            <span class="text-slate-700">{{ optional($record->borrowed_at)->format('d M Y') ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-400">เธเธณเธซเธเธ”เธเธทเธ</span>
                            <span class="{{ $overdueDays > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $record->due_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-400">เธเนเธฒเธเธฃเธฑเธเธเธณเธเธงเธ“</span>
                            <span class="{{ $fineAmount > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ number_format($fineAmount, 2) }} เธเธฒเธ—</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button wire:click="openReturnModal({{ $record->id }})" class="flex w-full items-center justify-center gap-3 rounded-2xl {{ $fineAmount > 0 ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-100' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-100' }} px-6 py-4 text-sm font-black text-white shadow-lg transition-all">
                            <i class="fa-solid {{ $fineAmount > 0 ? 'fa-coins' : 'fa-rotate-left' }}"></i>
                            <span>{{ $fineAmount > 0 ? 'เธเธทเธเธเธญเธ / เธเธณเธฃเธฐเธเนเธฒเธเธฃเธฑเธ' : 'เธฃเธฑเธเธเธทเธเธญเธธเธเธเธฃเธ“เน' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center opacity-40">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300 shadow-inner">
                        <i class="fa-solid fa-box-open text-3xl"></i>
                    </div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-slate-400">เนเธกเนเธกเธตเธฃเธฒเธขเธเธฒเธฃเธขเธทเธกเธ—เธตเนเธ•เนเธญเธเธฃเธฑเธเธเธทเธเธ•เธญเธเธเธตเน</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm font-bold text-slate-500">
                เธซเธเนเธฒ {{ $records->lastPage() > 0 ? $records->currentPage() : 0 }} / {{ $records->lastPage() }} ยท เธฃเธงเธก {{ number_format($records->total()) }} เธฃเธฒเธขเธเธฒเธฃ
            </p>
            @if($records->hasPages())
                {{ $records->links() }}
            @endif
        </div>
    </div>

    @if($canProcessReturns && $showReturnModal && $selectedRecord)
        @php
            $modalOverdueDays = $this->overdueDays($selectedRecord);
            $modalFineAmount = $this->calculatedFineAmount($selectedRecord);
        @endphp
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeReturnModal"></div>
            <div class="relative w-full max-w-3xl rounded-[3rem] bg-white p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800">Return Equipment</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">{{ $selectedRecord->item?->name ?? $selectedRecord->category?->name ?? 'Unknown item' }}</p>
                    </div>
                    <button wire:click="closeReturnModal" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-[2rem] border border-slate-100 bg-slate-50 p-6">
                        <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">เธเธนเนเธขเธทเธก</p>
                        <h4 class="text-xl font-black text-slate-900">{{ $selectedRecord->borrower?->full_name ?: $selectedRecord->borrower?->name ?: 'Unknown user' }}</h4>
                        <p class="mt-2 text-sm font-bold text-slate-500">{{ $selectedRecord->borrower?->identity_label ?? 'Identity' }}: {{ $selectedRecord->borrower?->identity_value ?? '-' }}</p>
                    </div>

                    <div class="rounded-[2rem] border border-slate-100 bg-slate-50 p-6">
                        <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">เธชเธ–เธฒเธเธฐเธเนเธฒเธเธฃเธฑเธ</p>
                        <h4 class="text-xl font-black {{ $modalFineAmount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $modalFineAmount > 0 ? number_format($modalFineAmount, 2).' เธเธฒเธ—' : 'เนเธกเนเธกเธตเธเนเธฒเธเธฃเธฑเธ' }}
                        </h4>
                        <p class="mt-2 text-sm font-bold text-slate-500">
                            {{ $modalOverdueDays > 0 ? 'เน€เธเธดเธเธเธณเธซเธเธ” '.$modalOverdueDays.' เธงเธฑเธ' : 'เธเธทเธเธ•เธฒเธกเธเธณเธซเธเธ”' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เธเธฑเธเธ—เธถเธเธเธฒเธฃเธฃเธฑเธเธเธทเธ</label>
                        <textarea wire:model="returnNotes" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50"></textarea>
                        @error('returnNotes') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if($modalFineAmount > 0)
                        <label class="inline-flex items-center gap-3 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                            <input wire:model.live="collectFineNow" type="checkbox" class="h-5 w-5 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                            <span>เธฃเธฑเธเธเธณเธฃเธฐเธเนเธฒเธเธฃเธฑเธเธ—เธฑเธเธ—เธต</span>
                        </label>

                        @if($collectFineNow)
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เธขเธญเธ”เธเธณเธฃเธฐ</label>
                                    <input wire:model="amountPaid" type="number" step="0.01" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                                    @error('amountPaid') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เธงเธดเธเธตเธเธณเธฃเธฐเน€เธเธดเธ</label>
                                    <select wire:model="paymentMethod" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                                        <option value="cash">cash</option>
                                        <option value="bank_transfer">bank_transfer</option>
                                    </select>
                                    @error('paymentMethod') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เธซเธกเธฒเธขเน€เธซเธ•เธธเธเธฒเธฃเธเธณเธฃเธฐ</label>
                                    <textarea wire:model="paymentNotes" rows="3" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50"></textarea>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="processReturn" class="flex-1 rounded-2xl {{ $modalFineAmount > 0 ? 'bg-rose-600' : 'bg-blue-600' }} px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl transition-all active:scale-95">
                        {{ $modalFineAmount > 0 ? 'เธขเธทเธเธขเธฑเธเธเธทเธเธเธญเธเนเธฅเธฐเธเธฑเธเธ—เธถเธเธเนเธฒเธเธฃเธฑเธ' : 'เธขเธทเธเธขเธฑเธเธฃเธฑเธเธเธทเธเธญเธธเธเธเธฃเธ“เน' }}
                    </button>
                    <button wire:click="closeReturnModal" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-500 transition-all active:scale-95">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
