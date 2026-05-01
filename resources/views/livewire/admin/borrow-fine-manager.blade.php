@php
    $adminUser = Auth::guard('admin')->user();
    $canCollectFines = ! $adminUser || $adminUser->hasActionAccess('borrow.fine.collect');
@endphp
<div class="space-y-8 animate-in fade-in duration-700">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2.5rem] border border-amber-100 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            ระบบจัดการค่าปรับยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน `php artisan migrate` เพื่อสร้างตาราง borrow ก่อน
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 shadow-inner">
                    <i class="fa-solid fa-hourglass-half text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ค่าปรับค้าง</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['pending']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 shadow-inner">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ชำระแล้ว</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['paid']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 shadow-inner">
                    <i class="fa-solid fa-coins text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ยอดค้างชำระ</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($stats['pending_amount'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 p-6 shadow-xl">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/5 blur-2xl"></div>
            <div class="relative flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white shadow-inner">
                    <i class="fa-solid fa-wallet text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-white/40">รับชำระสะสม</p>
                    <h4 class="text-2xl font-black text-white">{{ number_format($stats['collected_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Outstanding Fines</h3>
                <p class="mt-1 text-sm font-bold text-slate-400">ติดตามรายการค่าปรับที่ยังไม่ได้รับชำระ</p>
            </div>
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="fineSearch" type="text" placeholder="ค้นหาผู้ยืม อุปกรณ์ หรือ serial..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-6 py-5">ผู้ยืม</th>
                        <th class="px-6 py-5">อุปกรณ์</th>
                        <th class="px-6 py-5">ยอดค่าปรับ</th>
                        <th class="px-6 py-5">สถานะ</th>
                        <th class="px-6 py-5 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($pendingFines as $fine)
                        <tr class="group hover:bg-slate-50/40 transition-all">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $fine->record?->borrower?->full_name ?: $fine->record?->borrower?->name ?: 'Unknown user' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $fine->record?->borrower?->identity_value ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $fine->record?->item?->name ?? $fine->record?->category?->name ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Record #{{ $fine->borrow_record_id }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-black text-rose-600">{{ number_format((float) $fine->amount, 2) }} บาท</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="rounded-full bg-amber-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-600">{{ $fine->status }}</span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($canCollectFines)
                                    <button wire:click="openPaymentModal({{ $fine->id }})" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                        <span>Record Payment</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-40">ไม่มีค่าปรับค้างชำระ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm font-bold text-slate-500">
                หน้า {{ $pendingFines->lastPage() > 0 ? $pendingFines->currentPage() : 0 }} / {{ $pendingFines->lastPage() }} · รวม {{ number_format($pendingFines->total()) }} รายการ
            </p>
            @if($pendingFines->hasPages())
                {{ $pendingFines->links() }}
            @endif
        </div>
    </div>

    <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Payment History</h3>
                <p class="mt-1 text-sm font-bold text-slate-400">ประวัติการรับเงินค่าปรับและเลขที่ใบเสร็จ</p>
            </div>
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="paymentSearch" type="text" placeholder="ค้นหาผู้ยืม อุปกรณ์ หรือ receipt..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-6 py-5">ผู้ยืม</th>
                        <th class="px-6 py-5">อุปกรณ์</th>
                        <th class="px-6 py-5">ยอดชำระ</th>
                        <th class="px-6 py-5">วิธีชำระ</th>
                        <th class="px-6 py-5">เลขที่ใบเสร็จ</th>
                        <th class="px-6 py-5">วันที่ชำระ</th>
                        <th class="px-6 py-5 text-right">เอกสาร</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($paymentHistory as $payment)
                        <tr class="hover:bg-slate-50/40 transition-all">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $payment->fine?->record?->borrower?->full_name ?: $payment->fine?->record?->borrower?->name ?: 'Unknown user' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $payment->fine?->record?->borrower?->identity_value ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $payment->fine?->record?->item?->name ?? $payment->fine?->record?->category?->name ?? '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Fine #{{ $payment->fine_id }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-black text-emerald-600">{{ number_format((float) $payment->amount_paid, 2) }} บาท</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="rounded-full bg-cyan-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-cyan-600">{{ $payment->payment_method }}</span>
                            </td>
                            <td class="px-6 py-5 font-black text-slate-700">{{ $payment->receipt_number ?: '-' }}</td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-700">{{ optional($payment->payment_date)->format('d M Y') ?: '-' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ optional($payment->payment_date)->format('H:i') ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($canCollectFines)
                                    <a href="{{ route('admin.borrow_payments.receipt', $payment) }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-white transition-all hover:bg-slate-800">
                                        <i class="fa-solid fa-print"></i>
                                        <span>Receipt</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-40">ยังไม่มีประวัติการรับเงินค่าปรับ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm font-bold text-slate-500">
                หน้า {{ $paymentHistory->lastPage() > 0 ? $paymentHistory->currentPage() : 0 }} / {{ $paymentHistory->lastPage() }} · รวม {{ number_format($paymentHistory->total()) }} รายการ
            </p>
            @if($paymentHistory->hasPages())
                {{ $paymentHistory->links() }}
            @endif
        </div>
    </div>

    @if($canCollectFines && $showPaymentModal && $selectedFine)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentModal"></div>
            <div class="relative w-full max-w-2xl rounded-[3rem] bg-white p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800">Record Fine Payment</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">{{ $selectedFine->record?->borrower?->full_name ?: $selectedFine->record?->borrower?->name ?: 'Unknown user' }}</p>
                    </div>
                    <button wire:click="closePaymentModal" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="rounded-[2rem] border border-slate-100 bg-slate-50 p-6">
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">ยอดค่าปรับ</p>
                    <h4 class="text-2xl font-black text-rose-600">{{ number_format((float) $selectedFine->amount, 2) }} บาท</h4>
                    <p class="mt-2 text-sm font-bold text-slate-500">{{ $selectedFine->record?->item?->name ?? $selectedFine->record?->category?->name ?? '-' }}</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">ยอดชำระ</label>
                        <input wire:model="amountPaid" type="number" step="0.01" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        @error('amountPaid') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">วิธีชำระเงิน</label>
                        <select wire:model="paymentMethod" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                            <option value="cash">cash</option>
                            <option value="bank_transfer">bank_transfer</option>
                        </select>
                        @error('paymentMethod') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">หมายเหตุ</label>
                        <textarea wire:model="paymentNotes" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50"></textarea>
                        @error('paymentNotes') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="recordPayment" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-emerald-100 transition-all active:scale-95">
                        Confirm Payment
                    </button>
                    <button wire:click="closePaymentModal" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-500 transition-all active:scale-95">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
