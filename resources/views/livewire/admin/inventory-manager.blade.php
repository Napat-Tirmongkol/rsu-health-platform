@php
    $adminUser = Auth::guard('admin')->user();
    $canManageInventory = ! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage');
@endphp

<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm font-bold text-amber-700 shadow-sm">
            ระบบ inventory ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน <code class="rounded bg-white px-2 py-1 text-xs font-black text-amber-700">php artisan migrate</code> เพื่อสร้างตารางของ e-Borrow ให้ครบก่อน
        </div>
    @endif

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Categories</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">หมวดอุปกรณ์</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($categoryStats['total']) }}</h3>
            <p class="mt-3 text-sm font-bold text-slate-500">จำนวนหมวดที่ใช้คุม inventory ทั้งหมด</p>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i class="fa-solid fa-bolt text-xl"></i>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Active</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">หมวดที่เปิดใช้งาน</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($categoryStats['active']) }}</h3>
            <p class="mt-3 text-sm font-bold text-slate-500">หมวดที่ยังพร้อมให้ใช้งานและสร้างรายการยืม</p>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Available</span>
            </div>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">พร้อมให้ยืม</p>
            <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($categoryStats['available_items']) }}</h3>
            <p class="mt-3 text-sm font-bold text-slate-500">จำนวนอุปกรณ์ที่ยังว่างสำหรับคำขอใหม่</p>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-900 bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
            <div class="bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.24),transparent_42%)] p-6">
                <div class="flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <i class="fa-solid fa-hand-holding text-xl"></i>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-white/55">Borrowed</span>
                </div>
                <p class="mt-6 text-[10px] font-black uppercase tracking-[0.28em] text-white/45">กำลังถูกยืม</p>
                <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ number_format($categoryStats['borrowed_items']) }}</h3>
                <p class="mt-3 text-sm font-bold text-white/72">ภาพรวมอุปกรณ์ที่อยู่ในรอบยืมปัจจุบัน</p>
            </div>
        </div>
    </section>

    <section class="rounded-[2.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Category Management</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Equipment Categories</h2>
                <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-slate-500">จัดการหมวดอุปกรณ์ คำอธิบาย และภาพรวมจำนวนพร้อมใช้เพื่อให้ทีม inventory เห็นสถานะจากมุมเดียว</p>
            </div>
            @if($canManageInventory)
                <button wire:click="openCreateCategory" class="inline-flex items-center gap-3 rounded-2xl bg-emerald-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Category</span>
                </button>
            @endif
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="categorySearch" type="text" placeholder="ค้นหาหมวดอุปกรณ์หรือคำอธิบาย" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
            </div>
            <p class="text-sm font-bold text-slate-500">หน้า {{ $categories->lastPage() > 0 ? $categories->currentPage() : 0 }} / {{ $categories->lastPage() }} · รวม {{ number_format($categories->total()) }} รายการ</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5">หมวดอุปกรณ์</th>
                        <th class="px-6 py-5">รายละเอียด</th>
                        <th class="px-6 py-5 text-center">พร้อมใช้ / ทั้งหมด</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-900">{{ $category->name }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-md truncate text-sm font-bold text-slate-500">{{ $category->description ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700">
                                    {{ $category->available_items_count }} / {{ $category->items_count }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] {{ $category->is_active ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $category->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($canManageInventory)
                                    <button wire:click="openEditCategory({{ $category->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-700">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-sm font-bold uppercase tracking-[0.22em] text-slate-400">ยังไม่มีหมวดอุปกรณ์</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-6 border-t border-slate-100 pt-5">
                {{ $categories->links() }}
            </div>
        @endif
    </section>

    <section class="rounded-[2.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Item Management</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Inventory Items</h2>
                <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-slate-500">คุมรายชื่ออุปกรณ์ รายละเอียด serial number และสถานะการใช้งานรายชิ้นสำหรับการยืมและคืนจริง</p>
            </div>
            @if($canManageInventory)
                <button wire:click="openCreateItem" class="inline-flex items-center gap-3 rounded-2xl bg-sky-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-sky-100 transition-all hover:bg-sky-700">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Item</span>
                </button>
            @endif
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr,0.8fr,0.8fr]">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="itemSearch" type="text" placeholder="ค้นหาชื่ออุปกรณ์ serial หรือชื่อหมวด" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
            </div>
            <select wire:model.live="itemCategoryFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                <option value="all">ทุกหมวดอุปกรณ์</option>
                @foreach($allCategories as $categoryOption)
                    <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="itemStatusFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                <option value="all">ทุกสถานะ</option>
                <option value="available">available</option>
                <option value="borrowed">borrowed</option>
                <option value="maintenance">maintenance</option>
            </select>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-500">หน้า {{ $items->lastPage() > 0 ? $items->currentPage() : 0 }} / {{ $items->lastPage() }} · รวม {{ number_format($items->total()) }} รายการ</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5">อุปกรณ์</th>
                        <th class="px-6 py-5">หมวด</th>
                        <th class="px-6 py-5">Serial Number</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-900">{{ $item->name }}</div>
                                <div class="max-w-md truncate text-sm font-bold text-slate-500">{{ $item->description ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-700">{{ $item->category?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-black text-slate-700">{{ $item->serial_number ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em]
                                    {{ $item->status === 'available' ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $item->status === 'borrowed' ? 'border border-cyan-200 bg-cyan-50 text-cyan-700' : '' }}
                                    {{ $item->status === 'maintenance' ? 'border border-amber-200 bg-amber-50 text-amber-700' : '' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($canManageInventory)
                                    <div class="inline-flex items-center gap-2">
                                        <button wire:click="openEditItem({{ $item->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-700">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button wire:click="deleteItem({{ $item->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition-all hover:bg-rose-100 hover:text-rose-700">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-sm font-bold uppercase tracking-[0.22em] text-slate-400">ยังไม่มีรายการอุปกรณ์</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="mt-6 border-t border-slate-100 pt-5">
                {{ $items->links() }}
            </div>
        @endif
    </section>

    @if($canManageInventory && $showCategoryModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="$set('showCategoryModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-[2.75rem] border border-slate-200 bg-white p-8 shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Category Form</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">กำหนดชื่อ คำอธิบาย และสถานะของหมวดอุปกรณ์</p>
                    </div>
                    <button wire:click="$set('showCategoryModal', false)" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mt-7 space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">ชื่อหมวด</label>
                        <input wire:model="categoryName" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('categoryName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">รายละเอียด</label>
                        <textarea wire:model="categoryDescription" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"></textarea>
                    </div>

                    <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <input wire:model="categoryIsActive" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>เปิดใช้งานหมวดนี้</span>
                    </label>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveCategory" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">
                        Save Category
                    </button>
                    <button wire:click="$set('showCategoryModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-slate-500 transition-all hover:bg-slate-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($canManageInventory && $showItemModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="$set('showItemModal', false)"></div>
            <div class="relative w-full max-w-3xl rounded-[2.75rem] border border-slate-200 bg-white p-8 shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Inventory Form</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $editingItemId ? 'Edit Item' : 'Add Item' }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">จัดการชื่ออุปกรณ์ serial number หมวด และสถานะการใช้งานรายชิ้น</p>
                    </div>
                    <button wire:click="$set('showItemModal', false)" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mt-7 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">หมวดอุปกรณ์</label>
                        <select wire:model="itemCategoryId" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                            <option value="">เลือกหมวดอุปกรณ์</option>
                            @foreach($allCategories as $categoryOption)
                                <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                            @endforeach
                        </select>
                        @error('itemCategoryId') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">สถานะ</label>
                        <select wire:model="itemStatus" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                            <option value="available">available</option>
                            <option value="borrowed">borrowed</option>
                            <option value="maintenance">maintenance</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">ชื่ออุปกรณ์</label>
                        <input wire:model="itemName" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('itemName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Serial Number</label>
                        <input wire:model="itemSerialNumber" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('itemSerialNumber') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">รายละเอียด</label>
                        <textarea wire:model="itemDescription" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveItem" class="flex-1 rounded-2xl bg-sky-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-sky-700">
                        Save Item
                    </button>
                    <button wire:click="$set('showItemModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-slate-500 transition-all hover:bg-slate-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
