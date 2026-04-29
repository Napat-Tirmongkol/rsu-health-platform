@php
    $adminUser = Auth::guard('admin')->user();
    $canManageInventory = ! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage');
@endphp
<div class="space-y-8 animate-in fade-in duration-700">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2.5rem] border border-amber-100 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            ระบบ walk-in borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน `php artisan migrate` เพื่อสร้างตาราง borrow ก่อน
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[0.95fr,1.15fr]">
        <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
            <div class="mb-6">
                <h3 class="text-xl font-black text-slate-800">Borrower</h3>
                <p class="mt-1 text-sm font-bold text-slate-400">ค้นหาผู้ยืมจากชื่อ รหัสนักศึกษา รหัสบุคลากร หรือ identity</p>
            </div>

            @if($selectedUser)
                <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-lg font-black text-slate-900">{{ $selectedUser->full_name ?: $selectedUser->name }}</h4>
                            <p class="mt-1 text-sm font-bold text-slate-500">{{ $selectedUser->identity_label }}: {{ $selectedUser->identity_value }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-400">{{ $selectedUser->department ?: '-' }}</p>
                        </div>
                        <button wire:click="clearSelectedUser" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            @else
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input wire:model.live.debounce.300ms="userSearch" type="text" placeholder="พิมพ์ชื่อ รหัสนักศึกษา หรือ identity..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($matchedUsers as $user)
                        <button wire:click="selectUser({{ $user->id }})" class="flex w-full items-start justify-between rounded-[1.75rem] border border-slate-100 bg-slate-50 p-5 text-left transition-all hover:border-emerald-200 hover:bg-emerald-50/40 {{ $canManageInventory ? '' : 'cursor-not-allowed opacity-60' }}" @if(! $canManageInventory) disabled @endif>
                            <div>
                                <h4 class="text-base font-black text-slate-900">{{ $user->full_name ?: $user->name }}</h4>
                                <p class="mt-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ $user->identity_label }}: {{ $user->identity_value }}</p>
                            </div>
                            <i class="fa-solid fa-chevron-right mt-1 text-slate-300"></i>
                        </button>
                    @empty
                        @if($userSearch !== '')
                            <div class="rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center text-sm font-bold text-slate-400">
                                ไม่พบผู้ใช้ที่ตรงกับคำค้นนี้
                            </div>
                        @endif
                    @endforelse
                </div>
            @endif

            <div class="mt-8 border-t border-slate-100 pt-8">
                <h3 class="text-xl font-black text-slate-800">Borrow Setup</h3>
                <div class="mt-5 space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">กำหนดคืน</label>
                        <input wire:model="dueDate" type="date" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        @error('dueDate') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">เหตุผล</label>
                        <textarea wire:model="reason" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50" placeholder="เช่น ยืมเพื่อใช้งานประกอบกิจกรรมหรือเรียนภาคปฏิบัติ"></textarea>
                        @error('reason') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Available Equipment</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">เลือกอุปกรณ์ที่พร้อมให้ยืม แล้วเพิ่มเข้ารายการด้านล่าง</p>
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr,0.8fr]">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input wire:model.live.debounce.300ms="itemSearch" type="text" placeholder="ค้นหาชื่ออุปกรณ์หรือ serial..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                    </div>
                    <select wire:model.live="itemCategoryFilter" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        <option value="all">ทุกหมวดอุปกรณ์</option>
                        @foreach($allCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @forelse($availableItems as $item)
                        <button wire:click="addItem({{ $item->id }})" class="rounded-[1.75rem] border border-slate-100 bg-slate-50 p-5 text-left transition-all hover:border-indigo-200 hover:bg-indigo-50/30 {{ $canManageInventory ? '' : 'cursor-not-allowed opacity-60' }}" @if(! $canManageInventory) disabled @endif>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-black text-slate-900">{{ $item->name }}</h4>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ $item->category?->name ?? '-' }}</p>
                                    <p class="mt-2 text-sm font-bold text-slate-500">S/N: {{ $item->serial_number ?: '-' }}</p>
                                </div>
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-500 shadow-sm">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center text-sm font-bold text-slate-400">
                            ไม่พบอุปกรณ์ที่พร้อมให้ยืมตามเงื่อนไขนี้
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Borrow Cart</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">รายการอุปกรณ์ที่จะเปิดยืมแบบ walk-in</p>
                    </div>
                    <div class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-500">
                        {{ count($cartItemIds) }} items
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($cartItems as $item)
                        <div class="flex items-center justify-between gap-4 rounded-[1.75rem] border border-slate-100 bg-slate-50 px-5 py-4">
                            <div>
                                <h4 class="text-base font-black text-slate-900">{{ $item->name }}</h4>
                                <p class="mt-1 text-xs font-bold uppercase tracking-widest text-slate-400">{{ $item->category?->name ?? '-' }}</p>
                                <p class="mt-2 text-sm font-bold text-slate-500">S/N: {{ $item->serial_number ?: '-' }}</p>
                            </div>
                            @if($canManageInventory)
                                <button wire:click="removeItem({{ $item->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition-all hover:bg-rose-100 hover:text-rose-600">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center text-sm font-bold text-slate-400">
                            ยังไม่มีอุปกรณ์ในรายการยืม
                        </div>
                    @endforelse
                </div>

                @error('cart') <p class="mt-4 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                @error('selectedUserId') <p class="mt-4 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror

                <div class="mt-8">
                    @if($canManageInventory)
                        <button wire:click="submitWalkInBorrow" class="w-full rounded-2xl bg-indigo-600 px-6 py-4 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-100 transition-all active:scale-95">
                            Confirm Walk-In Borrow
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
