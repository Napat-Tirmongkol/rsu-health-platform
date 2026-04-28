<div class="space-y-8 animate-in fade-in duration-700">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700 shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2.5rem] border border-amber-100 bg-amber-50 px-8 py-6 text-sm font-bold text-amber-700 shadow-sm">
            ระบบ inventory ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน `php artisan migrate` เพื่อสร้างตาราง borrow ก่อน
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 shadow-inner">
                    <i class="fa-solid fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">หมวดอุปกรณ์</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($categoryStats['total']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 shadow-inner">
                    <i class="fa-solid fa-bolt text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">หมวดที่เปิดใช้งาน</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($categoryStats['active']) }}</h4>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-500 shadow-inner">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">พร้อมให้ยืม</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ number_format($categoryStats['available_items']) }}</h4>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 p-6 shadow-xl">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/5 blur-2xl"></div>
            <div class="relative flex items-center gap-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white shadow-inner">
                    <i class="fa-solid fa-hand-holding text-2xl"></i>
                </div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-white/40">กำลังถูกยืม</p>
                    <h4 class="text-2xl font-black text-white">{{ number_format($categoryStats['borrowed_items']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Equipment Categories</h3>
                <p class="mt-1 text-sm font-bold text-slate-400">จัดการหมวดอุปกรณ์และติดตามจำนวนของที่พร้อมใช้งาน</p>
            </div>
            <button wire:click="openCreateCategory" class="inline-flex items-center gap-3 rounded-2xl bg-[#2e9e63] px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                <span>Add Category</span>
            </button>
        </div>

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="categorySearch" type="text" placeholder="ค้นหาหมวดอุปกรณ์..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-6 py-5">หมวดอุปกรณ์</th>
                        <th class="px-6 py-5">รายละเอียด</th>
                        <th class="px-6 py-5 text-center">จำนวนพร้อมใช้ / ทั้งหมด</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($categories as $category)
                        <tr class="group hover:bg-slate-50/40 transition-all">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $category->name }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-md truncate text-sm font-bold text-slate-500">{{ $category->description ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full bg-emerald-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-600">
                                    {{ $category->available_items_count }} / {{ $category->items_count }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $category->is_active ? 'bg-cyan-50 text-cyan-600' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $category->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <button wire:click="openEditCategory({{ $category->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-40">ยังไม่มีหมวดอุปกรณ์</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm font-bold text-slate-500">
                หน้า {{ $categories->lastPage() > 0 ? $categories->currentPage() : 0 }} / {{ $categories->lastPage() }} · รวม {{ number_format($categories->total()) }} รายการ
            </p>
            @if($categories->hasPages())
                {{ $categories->links() }}
            @endif
        </div>
    </div>

    <div class="rounded-[3rem] border border-slate-100 bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Inventory Items</h3>
                <p class="mt-1 text-sm font-bold text-slate-400">จัดการอุปกรณ์รายชิ้น สถานะ และ serial number</p>
            </div>
            <button wire:click="openCreateItem" class="inline-flex items-center gap-3 rounded-2xl bg-indigo-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700">
                <i class="fa-solid fa-plus"></i>
                <span>Add Item</span>
            </button>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr,0.8fr,0.8fr]">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="itemSearch" type="text" placeholder="ค้นหาชื่ออุปกรณ์หรือ serial..." class="w-full rounded-2xl border border-slate-100 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
            </div>
            <select wire:model.live="itemCategoryFilter" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                <option value="all">ทุกหมวดอุปกรณ์</option>
                @foreach($allCategories as $categoryOption)
                    <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="itemStatusFilter" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                <option value="all">ทุกสถานะ</option>
                <option value="available">available</option>
                <option value="borrowed">borrowed</option>
                <option value="maintenance">maintenance</option>
            </select>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <th class="px-6 py-5">อุปกรณ์</th>
                        <th class="px-6 py-5">หมวด</th>
                        <th class="px-6 py-5">Serial Number</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                        <tr class="group hover:bg-slate-50/40 transition-all">
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-800">{{ $item->name }}</div>
                                <div class="max-w-md truncate text-sm font-bold text-slate-500">{{ $item->description ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-700">{{ $item->category?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-black text-slate-700">{{ $item->serial_number ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-widest
                                    {{ $item->status === 'available' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $item->status === 'borrowed' ? 'bg-cyan-50 text-cyan-600' : '' }}
                                    {{ $item->status === 'maintenance' ? 'bg-amber-50 text-amber-600' : '' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="openEditItem({{ $item->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button wire:click="deleteItem({{ $item->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition-all hover:bg-rose-100 hover:text-rose-600">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-400 opacity-40">ยังไม่มีรายการอุปกรณ์</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm font-bold text-slate-500">
                หน้า {{ $items->lastPage() > 0 ? $items->currentPage() : 0 }} / {{ $items->lastPage() }} · รวม {{ number_format($items->total()) }} รายการ
            </p>
            @if($items->hasPages())
                {{ $items->links() }}
            @endif
        </div>
    </div>

    @if($showCategoryModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showCategoryModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-[3rem] bg-white p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800">{{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">กำหนดหมวดอุปกรณ์และสถานะการใช้งาน</p>
                    </div>
                    <button wire:click="$set('showCategoryModal', false)" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">ชื่อหมวด</label>
                        <input wire:model="categoryName" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        @error('categoryName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">รายละเอียด</label>
                        <textarea wire:model="categoryDescription" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50"></textarea>
                    </div>

                    <label class="inline-flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <input wire:model="categoryIsActive" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-[#2e9e63] focus:ring-[#2e9e63]">
                        <span>เปิดใช้งานหมวดนี้</span>
                    </label>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveCategory" class="flex-1 rounded-2xl bg-[#2e9e63] px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-green-100 transition-all active:scale-95">
                        Save Category
                    </button>
                    <button wire:click="$set('showCategoryModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-500 transition-all active:scale-95">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showItemModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showItemModal', false)"></div>
            <div class="relative w-full max-w-3xl rounded-[3rem] bg-white p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800">{{ $editingItemId ? 'Edit Item' : 'Add Item' }}</h3>
                        <p class="mt-1 text-sm font-bold text-slate-400">จัดการชื่ออุปกรณ์ serial number และสถานะการใช้งาน</p>
                    </div>
                    <button wire:click="$set('showItemModal', false)" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">หมวดอุปกรณ์</label>
                        <select wire:model="itemCategoryId" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                            <option value="">เลือกหมวดอุปกรณ์</option>
                            @foreach($allCategories as $categoryOption)
                                <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                            @endforeach
                        </select>
                        @error('itemCategoryId') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">สถานะ</label>
                        <select wire:model="itemStatus" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                            <option value="available">available</option>
                            <option value="borrowed">borrowed</option>
                            <option value="maintenance">maintenance</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">ชื่ออุปกรณ์</label>
                        <input wire:model="itemName" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        @error('itemName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Serial Number</label>
                        <input wire:model="itemSerialNumber" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50">
                        @error('itemSerialNumber') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">รายละเอียด</label>
                        <textarea wire:model="itemDescription" rows="4" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-bold outline-none transition-all focus:border-[#2e9e63] focus:bg-white focus:ring-4 focus:ring-green-50"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveItem" class="flex-1 rounded-2xl bg-indigo-600 px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-indigo-100 transition-all active:scale-95">
                        Save Item
                    </button>
                    <button wire:click="$set('showItemModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-500 transition-all active:scale-95">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
