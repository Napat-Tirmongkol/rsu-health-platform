@php
    $adminUser = Auth::guard('admin')->user();
    $canManageInventory = ! $adminUser || $adminUser->hasActionAccess('borrow.inventory.manage');
@endphp

<div class="space-y-8">
    @if (session()->has('message'))
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-bold text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    @if (! $tablesReady)
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm font-bold text-amber-700">
            เธฃเธฐเธเธ inventory เธขเธฑเธเนเธกเนเธเธฃเนเธญเธกเนเธเนเธเธฒเธเน€เธ•เนเธกเธฃเธนเธเนเธเธ เธเธฃเธธเธ“เธฒเธฃเธฑเธ <code class="rounded bg-white px-2 py-1 text-xs font-black text-amber-700">php artisan migrate</code> เน€เธเธทเนเธญเธชเธฃเนเธฒเธเธ•เธฒเธฃเธฒเธเธเธญเธ e-Borrow เนเธซเนเธเธฃเธเธเนเธญเธ
        </div>
    @endif

    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
        <x-admin.stat-card icon="fa-layer-group" badge="Categories" eyebrow="หมวดอุปกรณ์" :value="number_format($categoryStats['total'])" description="จำนวนหมวดทั้งหมดที่ใช้จัดกลุ่มอุปกรณ์ในคลัง" variant="info" />

        <x-admin.stat-card icon="fa-bolt" badge="Active" eyebrow="หมวดที่เปิดใช้งาน" :value="number_format($categoryStats['active'])" description="หมวดที่ยังพร้อมใช้งานและเปิดให้ทีมสร้างรายการยืมได้" variant="success" />

        <x-admin.stat-card icon="fa-box-open" badge="Available" eyebrow="พร้อมให้ยืม" :value="number_format($categoryStats['available_items'])" description="จำนวนอุปกรณ์ที่ยังว่างพร้อมใช้งานสำหรับคำขอใหม่" variant="info" />

        <x-admin.stat-card icon="fa-hand-holding" badge="Borrowed" eyebrow="กำลังถูกยืม" :value="number_format($categoryStats['borrowed_items'])" description="ภาพรวมอุปกรณ์ที่อยู่ในรอบยืมปัจจุบันและยังไม่ถูกคืนเข้าคลัง" variant="soft-cyan" />
    </section>

    <section class="rounded-[2.75rem] border border-emerald-100 bg-white p-7 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-3 rounded-full bg-cyan-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-cyan-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                    Category Management
                </div>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">เธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</h2>
                <p class="mt-3 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                    เธเธฑเธ”เธเธฒเธฃเธเธทเนเธญเธซเธกเธงเธ” เธเธณเธญเธเธดเธเธฒเธข เธฃเธนเธเธ เธฒเธ เนเธฅเธฐเธชเธ–เธฒเธเธฐเธเธฒเธฃเนเธเนเธเธฒเธ เน€เธเธทเนเธญเนเธซเนเธ—เธตเธก inventory เนเธขเธเธเธฃเธฐเน€เธ เธ—เนเธฅเธฐเธ”เธนเธเธณเธเธงเธเธเธเน€เธซเธฅเธทเธญเนเธ”เนเน€เธฃเนเธงเธเธถเนเธ
                </p>
            </div>

            @if($canManageInventory)
                <button wire:click="openCreateCategory" class="inline-flex items-center gap-3 rounded-2xl bg-emerald-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>
                    <span>เน€เธเธดเนเธกเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</span>
                </button>
            @endif
        </div>

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="categorySearch" type="text" placeholder="เธเนเธเธซเธฒเธเธทเนเธญเธซเธกเธงเธ”เธซเธฃเธทเธญเธเธณเธญเธเธดเธเธฒเธข" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
            </div>
            <p class="text-sm font-bold text-slate-500">เธซเธเนเธฒ {{ $categories->lastPage() > 0 ? $categories->currentPage() : 0 }} / {{ $categories->lastPage() }} ยท เธฃเธงเธก {{ number_format($categories->total()) }} เธฃเธฒเธขเธเธฒเธฃ</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f7fbf8] text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5">เธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</th>
                        <th class="px-6 py-5">เธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”</th>
                        <th class="px-6 py-5 text-center">เธเธฃเนเธญเธกเนเธเน / เธ—เธฑเนเธเธซเธกเธ”</th>
                        <th class="px-6 py-5 text-center">เธชเธ–เธฒเธเธฐ</th>
                        <th class="px-6 py-5 text-right">เธเธฒเธฃเธเธฑเธ”เธเธฒเธฃ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="transition-colors hover:bg-[#fbfefc]">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    @if($category->image_url)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">
                                    @else
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-600">
                                            <i class="fa-solid fa-box-open text-lg"></i>
                                        </div>
                                    @endif
                                    <div class="font-black text-slate-900">{{ $category->name }}</div>
                                </div>
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
                                <span class="rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] {{ $category->is_active ? 'border border-cyan-200 bg-cyan-50 text-cyan-700' : 'border border-slate-200 bg-slate-100 text-slate-500' }}">
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
                            <td colspan="5" class="px-6 py-20 text-center text-sm font-bold uppercase tracking-[0.22em] text-slate-400">เธขเธฑเธเนเธกเนเธกเธตเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</td>
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

    <section class="rounded-[2.75rem] border border-emerald-100 bg-white p-7 shadow-sm lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-3 rounded-full bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Item Management
                </div>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950">เธฃเธฒเธขเธเธฒเธฃเธญเธธเธเธเธฃเธ“เนเนเธเธเธฅเธฑเธ</h2>
                <p class="mt-3 max-w-3xl text-sm font-bold leading-relaxed text-slate-500">
                    เธ•เธดเธ”เธ•เธฒเธกเธเธทเนเธญเธญเธธเธเธเธฃเธ“เน เธซเธกเธงเธ” Serial Number เนเธฅเธฐเธชเธ–เธฒเธเธฐเธเธฒเธฃเนเธเนเธเธฒเธเธฃเธฒเธขเธเธดเนเธ เน€เธเธทเนเธญเนเธซเนเธเธฒเธฃเธขเธทเธก เธเธทเธ เนเธฅเธฐเธเนเธญเธกเธเธณเธฃเธธเธเน€เธซเนเธเธ เธฒเธเธ•เธฃเธเธเธฑเธเธ—เธธเธเธเธ
                </p>
            </div>
            @if($canManageInventory)
                <button wire:click="openCreateItem" class="inline-flex items-center gap-3 rounded-2xl bg-cyan-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-cyan-700">
                    <i class="fa-solid fa-plus"></i>
                    <span>เน€เธเธดเนเธกเธญเธธเธเธเธฃเธ“เน</span>
                </button>
            @endif
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr,0.8fr,0.8fr]">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input wire:model.live.debounce.300ms="itemSearch" type="text" placeholder="เธเนเธเธซเธฒเธเธทเนเธญเธญเธธเธเธเธฃเธ“เน serial เธซเธฃเธทเธญเธเธทเนเธญเธซเธกเธงเธ”" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-4 pl-14 pr-6 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
            </div>
            <select wire:model.live="itemCategoryFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                <option value="all">เธ—เธธเธเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</option>
                @foreach($allCategories as $categoryOption)
                    <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="itemStatusFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                <option value="all">เธ—เธธเธเธชเธ–เธฒเธเธฐ</option>
                <option value="available">available</option>
                <option value="borrowed">borrowed</option>
                <option value="maintenance">maintenance</option>
            </select>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-500">เธซเธเนเธฒ {{ $items->lastPage() > 0 ? $items->currentPage() : 0 }} / {{ $items->lastPage() }} ยท เธฃเธงเธก {{ number_format($items->total()) }} เธฃเธฒเธขเธเธฒเธฃ</p>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full whitespace-nowrap text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-[#f7fbf8] text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                        <th class="px-6 py-5">เธญเธธเธเธเธฃเธ“เน</th>
                        <th class="px-6 py-5">เธซเธกเธงเธ”</th>
                        <th class="px-6 py-5">Serial Number</th>
                        <th class="px-6 py-5 text-center">เธชเธ–เธฒเธเธฐ</th>
                        <th class="px-6 py-5 text-right">เธเธฒเธฃเธเธฑเธ”เธเธฒเธฃ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="transition-colors hover:bg-[#fbfefc]">
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
                                        <button wire:click="deleteItem({{ $item->id }})" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition-all hover:bg-rose-100">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-sm font-bold uppercase tracking-[0.22em] text-slate-400">เธขเธฑเธเนเธกเนเธกเธตเธฃเธฒเธขเธเธฒเธฃเธญเธธเธเธเธฃเธ“เน</td>
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
            <div class="relative w-full max-w-2xl rounded-[2.75rem] border border-emerald-100 bg-white p-8 shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Category Form</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $editingCategoryId ? 'เนเธเนเนเธเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน' : 'เน€เธเธดเนเธกเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน' }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">เธเธณเธซเธเธ”เธเธทเนเธญ เธเธณเธญเธเธดเธเธฒเธข เธฃเธนเธเธ เธฒเธ เนเธฅเธฐเธชเธ–เธฒเธเธฐเธเธญเธเธซเธกเธงเธ”เนเธซเนเธเธฃเนเธญเธกเนเธเนเธเธฒเธ</p>
                    </div>
                    <button wire:click="$set('showCategoryModal', false)" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mt-7 space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธเธทเนเธญเธซเธกเธงเธ”</label>
                        <input wire:model="categoryName" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('categoryName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”</label>
                        <textarea wire:model="categoryDescription" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธฃเธนเธเธ เธฒเธเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</label>
                        <input wire:model="categoryImage" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white">
                        <p class="mt-2 text-xs font-bold text-slate-400">เธฃเธญเธเธฃเธฑเธ JPG, PNG, WEBP เธเธเธฒเธ”เนเธกเนเน€เธเธดเธ 2MB</p>
                        @error('categoryImage') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if ($categoryImage)
                        <div>
                            <p class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Preview</p>
                            <img src="{{ $categoryImage->temporaryUrl() }}" alt="Category preview" class="h-40 w-full rounded-2xl object-cover ring-1 ring-slate-200">
                        </div>
                    @elseif ($existingCategoryImagePath)
                        <div>
                            <p class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธฃเธนเธเธเธฑเธเธเธธเธเธฑเธ</p>
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingCategoryImagePath) }}" alt="Current category image" class="h-40 w-full rounded-2xl object-cover ring-1 ring-slate-200">
                        </div>
                    @endif

                    <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-[#f7fbf8] px-4 py-3 text-sm font-bold text-slate-700">
                        <input wire:model="categoryIsActive" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>เน€เธเธดเธ”เนเธเนเธเธฒเธเธซเธกเธงเธ”เธเธตเน</span>
                    </label>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveCategory" class="flex-1 rounded-2xl bg-emerald-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-emerald-700">
                        เธเธฑเธเธ—เธถเธเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน
                    </button>
                    <button wire:click="$set('showCategoryModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-slate-500 transition-all hover:bg-slate-50">
                        เธขเธเน€เธฅเธดเธ
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($canManageInventory && $showItemModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" wire:click="$set('showItemModal', false)"></div>
            <div class="relative w-full max-w-3xl rounded-[2.75rem] border border-emerald-100 bg-white p-8 shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Inventory Form</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $editingItemId ? 'เนเธเนเนเธเธญเธธเธเธเธฃเธ“เน' : 'เน€เธเธดเนเธกเธญเธธเธเธเธฃเธ“เน' }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">เธเธฑเธ”เธเธฒเธฃเธเธทเนเธญ เธซเธกเธงเธ” Serial Number เนเธฅเธฐเธชเธ–เธฒเธเธฐเธเธญเธเธญเธธเธเธเธฃเธ“เนเนเธ•เนเธฅเธฐเธเธดเนเธ</p>
                    </div>
                    <button wire:click="$set('showItemModal', false)" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mt-7 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</label>
                        <select wire:model="itemCategoryId" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                            <option value="">เน€เธฅเธทเธญเธเธซเธกเธงเธ”เธญเธธเธเธเธฃเธ“เน</option>
                            @foreach($allCategories as $categoryOption)
                                <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                            @endforeach
                        </select>
                        @error('itemCategoryId') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธชเธ–เธฒเธเธฐ</label>
                        <select wire:model="itemStatus" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                            <option value="available">available</option>
                            <option value="borrowed">borrowed</option>
                            <option value="maintenance">maintenance</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธเธทเนเธญเธญเธธเธเธเธฃเธ“เน</label>
                        <input wire:model="itemName" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('itemName') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">Serial Number</label>
                        <input wire:model="itemSerialNumber" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50">
                        @error('itemSerialNumber') <p class="mt-2 text-sm font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-slate-500">เธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”</label>
                        <textarea wire:model="itemDescription" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 outline-none transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button wire:click="saveItem" class="flex-1 rounded-2xl bg-cyan-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-cyan-700">
                        เธเธฑเธเธ—เธถเธเธญเธธเธเธเธฃเธ“เน
                    </button>
                    <button wire:click="$set('showItemModal', false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-slate-500 transition-all hover:bg-slate-50">
                        เธขเธเน€เธฅเธดเธ
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
