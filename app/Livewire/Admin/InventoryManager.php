<?php

namespace App\Livewire\Admin;

use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class InventoryManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $categorySearch = '';
    public string $itemSearch = '';
    public string $itemStatusFilter = 'all';
    public string $itemCategoryFilter = 'all';

    public bool $showCategoryModal = false;
    public bool $showItemModal = false;
    public ?int $editingCategoryId = null;
    public ?int $editingItemId = null;

    public string $categoryName = '';
    public string $categoryDescription = '';
    public bool $categoryIsActive = true;
    public $categoryImage = null;
    public ?string $existingCategoryImagePath = null;

    public ?int $itemCategoryId = null;
    public string $itemName = '';
    public string $itemDescription = '';
    public string $itemSerialNumber = '';
    public string $itemStatus = 'available';

    protected function rules(): array
    {
        $serialUniqueRule = 'nullable|string|max:100';

        if ($this->tablesReady()) {
            $serialUniqueRule .= '|unique:borrow_items,serial_number';

            if ($this->editingItemId) {
                $serialUniqueRule .= ','.$this->editingItemId;
            }
        }

        return [
            'categoryName' => ['required', 'string', 'max:255'],
            'categoryDescription' => ['nullable', 'string', 'max:1000'],
            'categoryIsActive' => ['boolean'],
            'categoryImage' => ['nullable', 'image', 'max:2048'],
            'itemCategoryId' => ['required', 'integer'],
            'itemName' => ['required', 'string', 'max:255'],
            'itemDescription' => ['nullable', 'string', 'max:1000'],
            'itemSerialNumber' => explode('|', $serialUniqueRule),
            'itemStatus' => ['required', 'in:available,borrowed,maintenance'],
        ];
    }

    public function updatedCategorySearch(): void
    {
        $this->resetPage('categoriesPage');
    }

    public function updatedItemSearch(): void
    {
        $this->resetPage('itemsPage');
    }

    public function updatedItemStatusFilter(): void
    {
        $this->resetPage('itemsPage');
    }

    public function updatedItemCategoryFilter(): void
    {
        $this->resetPage('itemsPage');
    }

    public function openCreateCategory(): void
    {
        $this->authorizeAction('borrow.inventory.manage');
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    public function openEditCategory(int $categoryId): void
    {
        $this->authorizeAction('borrow.inventory.manage');

        $category = BorrowCategory::findOrFail($categoryId);

        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryDescription = $category->description ?? '';
        $this->categoryIsActive = (bool) $category->is_active;
        $this->existingCategoryImagePath = $category->image_path;
        $this->categoryImage = null;
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->authorizeAction('borrow.inventory.manage');

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ inventory ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');

            return;
        }

        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:255'],
            'categoryDescription' => ['nullable', 'string', 'max:1000'],
            'categoryIsActive' => ['boolean'],
            'categoryImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $isEditing = $this->editingCategoryId !== null;
        $category = $isEditing
            ? BorrowCategory::findOrFail($this->editingCategoryId)
            : new BorrowCategory();

        $category->fill([
            'name' => $validated['categoryName'],
            'description' => $validated['categoryDescription'] ?: null,
            'is_active' => $validated['categoryIsActive'],
        ]);

        if ($this->categoryImage) {
            $newImagePath = $this->categoryImage->store('borrow-categories', 'public');

            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->image_path = $newImagePath;
        }

        $category->save();

        $this->syncCategoryCounts($category);
        $this->showCategoryModal = false;
        $this->resetCategoryForm();

        session()->flash('message', $isEditing ? 'อัปเดตหมวดอุปกรณ์เรียบร้อยแล้ว' : 'เพิ่มหมวดอุปกรณ์เรียบร้อยแล้ว');
    }

    public function openCreateItem(): void
    {
        $this->authorizeAction('borrow.inventory.manage');
        $this->resetItemForm();
        $this->itemCategoryId = $this->defaultCategoryId();
        $this->showItemModal = true;
    }

    public function openEditItem(int $itemId): void
    {
        $this->authorizeAction('borrow.inventory.manage');

        $item = BorrowItem::findOrFail($itemId);

        $this->editingItemId = $item->id;
        $this->itemCategoryId = $item->category_id;
        $this->itemName = $item->name;
        $this->itemDescription = $item->description ?? '';
        $this->itemSerialNumber = $item->serial_number ?? '';
        $this->itemStatus = $item->status;
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        $this->authorizeAction('borrow.inventory.manage');

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ inventory ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');

            return;
        }

        $validated = $this->validate([
            'itemCategoryId' => ['required', 'integer', 'exists:borrow_categories,id'],
            'itemName' => ['required', 'string', 'max:255'],
            'itemDescription' => ['nullable', 'string', 'max:1000'],
            'itemSerialNumber' => $this->serialValidationRule(),
            'itemStatus' => ['required', 'in:available,borrowed,maintenance'],
        ]);

        $existingItem = $this->editingItemId ? BorrowItem::findOrFail($this->editingItemId) : null;
        $previousCategoryId = $existingItem?->category_id;
        $isEditing = $this->editingItemId !== null;

        $item = BorrowItem::updateOrCreate(
            ['id' => $this->editingItemId],
            [
                'category_id' => $validated['itemCategoryId'],
                'name' => $validated['itemName'],
                'description' => $validated['itemDescription'] ?: null,
                'serial_number' => $validated['itemSerialNumber'] ?: null,
                'status' => $validated['itemStatus'],
            ]
        );

        $this->syncCategoryCounts($item->category);

        if ($previousCategoryId && $previousCategoryId !== $item->category_id) {
            $previousCategory = BorrowCategory::find($previousCategoryId);

            if ($previousCategory) {
                $this->syncCategoryCounts($previousCategory);
            }
        }

        $this->showItemModal = false;
        $this->resetItemForm();

        session()->flash('message', $isEditing ? 'อัปเดตรายการอุปกรณ์เรียบร้อยแล้ว' : 'เพิ่มอุปกรณ์เรียบร้อยแล้ว');
    }

    public function deleteItem(int $itemId): void
    {
        $this->authorizeAction('borrow.inventory.manage');

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ inventory ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');

            return;
        }

        $item = BorrowItem::withCount([
            'records as active_records_count' => fn ($query) => $query->where('status', 'borrowed'),
        ])->findOrFail($itemId);

        if ($item->active_records_count > 0 || $item->status === 'borrowed') {
            session()->flash('message', 'ไม่สามารถลบอุปกรณ์ที่กำลังถูกยืมอยู่ได้');

            return;
        }

        $category = $item->category;
        $item->delete();

        if ($category) {
            $this->syncCategoryCounts($category);
        }

        session()->flash('message', 'ลบอุปกรณ์เรียบร้อยแล้ว');
    }

    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.inventory-manager', [
                'categoryStats' => ['total' => 0, 'active' => 0, 'available_items' => 0, 'borrowed_items' => 0],
                'categories' => $this->emptyPaginator('categoriesPage'),
                'items' => $this->emptyPaginator('itemsPage'),
                'allCategories' => collect(),
                'tablesReady' => false,
            ]);
        }

        try {
            $categoryQuery = BorrowCategory::query()
                ->withCount([
                    'items as items_count',
                    'items as available_items_count' => fn ($query) => $query->where('status', 'available'),
                ])
                ->when($this->categorySearch, function ($query) {
                    $term = '%'.$this->categorySearch.'%';

                    return $query->where('name', 'like', $term)->orWhere('description', 'like', $term);
                })
                ->orderBy('name');

            $itemQuery = BorrowItem::query()
                ->with('category')
                ->when($this->itemStatusFilter !== 'all', fn ($query) => $query->where('status', $this->itemStatusFilter))
                ->when($this->itemCategoryFilter !== 'all', fn ($query) => $query->where('category_id', $this->itemCategoryFilter))
                ->when($this->itemSearch, function ($query) {
                    $term = '%'.$this->itemSearch.'%';

                    return $query->where(function ($itemQuery) use ($term) {
                        $itemQuery
                            ->where('name', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $term));
                    });
                })
                ->orderBy('category_id')
                ->orderBy('name');

            $categoryStats = [
                'total' => BorrowCategory::count(),
                'active' => BorrowCategory::where('is_active', true)->count(),
                'available_items' => BorrowItem::where('status', 'available')->count(),
                'borrowed_items' => BorrowItem::where('status', 'borrowed')->count(),
            ];

            return view('livewire.admin.inventory-manager', [
                'categoryStats' => $categoryStats,
                'categories' => $categoryQuery->paginate(20, ['*'], 'categoriesPage'),
                'items' => $itemQuery->paginate(20, ['*'], 'itemsPage'),
                'allCategories' => BorrowCategory::orderBy('name')->get(['id', 'name']),
                'tablesReady' => true,
            ]);
        } catch (QueryException) {
            return view('livewire.admin.inventory-manager', [
                'categoryStats' => ['total' => 0, 'active' => 0, 'available_items' => 0, 'borrowed_items' => 0],
                'categories' => $this->emptyPaginator('categoriesPage'),
                'items' => $this->emptyPaginator('itemsPage'),
                'allCategories' => collect(),
                'tablesReady' => false,
            ]);
        }
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_categories', 'borrow_items'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function emptyPaginator(string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            new Collection(),
            0,
            20,
            LengthAwarePaginator::resolveCurrentPage($pageName),
            ['path' => request()->url(), 'pageName' => $pageName]
        );
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categoryIsActive = true;
        $this->categoryImage = null;
        $this->existingCategoryImagePath = null;
    }

    private function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->itemCategoryId = null;
        $this->itemName = '';
        $this->itemDescription = '';
        $this->itemSerialNumber = '';
        $this->itemStatus = 'available';
    }

    private function defaultCategoryId(): ?int
    {
        if ($this->itemCategoryFilter !== 'all') {
            return (int) $this->itemCategoryFilter;
        }

        return BorrowCategory::query()->value('id');
    }

    private function serialValidationRule(): array
    {
        $rule = 'nullable|string|max:100|unique:borrow_items,serial_number';

        if ($this->editingItemId) {
            $rule .= ','.$this->editingItemId;
        }

        return explode('|', $rule);
    }

    private function syncCategoryCounts(?BorrowCategory $category): void
    {
        if (! $category) {
            return;
        }

        $category->update([
            'total_quantity' => $category->items()->count(),
            'available_quantity' => $category->items()->where('status', 'available')->count(),
        ]);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(Auth::guard('admin')->user()?->hasActionAccess($action), 403);
    }
}
