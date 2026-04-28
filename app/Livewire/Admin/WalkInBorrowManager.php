<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use App\Models\BorrowRecord;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class WalkInBorrowManager extends Component
{
    public string $userSearch = '';
    public ?int $selectedUserId = null;
    public string $itemSearch = '';
    public string $itemCategoryFilter = 'all';
    public array $cartItemIds = [];
    public string $dueDate = '';
    public string $reason = '';

    public function mount(): void
    {
        $this->dueDate = now()->addDays(7)->toDateString();
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->userSearch = '';
    }

    public function clearSelectedUser(): void
    {
        $this->selectedUserId = null;
    }

    public function addItem(int $itemId): void
    {
        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ walk-in borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');
            return;
        }

        if (! in_array($itemId, $this->cartItemIds, true)) {
            $item = BorrowItem::find($itemId);

            if ($item && $item->status === 'available') {
                $this->cartItemIds[] = $itemId;
            }
        }
    }

    public function removeItem(int $itemId): void
    {
        $this->cartItemIds = array_values(array_filter(
            $this->cartItemIds,
            fn (int $existingId) => $existingId !== $itemId
        ));
    }

    public function submitWalkInBorrow(): void
    {
        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ walk-in borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');
            return;
        }

        $this->validate([
            'selectedUserId' => ['required', 'integer', 'exists:users,id'],
            'dueDate' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (count($this->cartItemIds) === 0) {
            $this->addError('cart', 'กรุณาเลือกอุปกรณ์อย่างน้อย 1 รายการ');
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $items = BorrowItem::with('category')
            ->whereIn('id', $this->cartItemIds)
            ->where('status', 'available')
            ->get();

        if ($items->count() !== count($this->cartItemIds)) {
            $this->addError('cart', 'มีอุปกรณ์บางรายการไม่พร้อมให้ยืมแล้ว กรุณาเลือกใหม่');
            return;
        }

        foreach ($items as $item) {
            BorrowRecord::create([
                'clinic_id' => $item->clinic_id,
                'category_id' => $item->category_id,
                'item_id' => $item->id,
                'borrower_user_id' => $user->id,
                'quantity' => 1,
                'reason' => $this->reason ?: 'Walk-in borrow',
                'borrowed_at' => now(),
                'due_date' => $this->dueDate,
                'status' => 'borrowed',
                'approval_status' => 'staff_added',
                'fine_status' => 'none',
                'notes' => '['.now()->format('Y-m-d H:i').'] Walk-in borrow created by admin '.(Auth::guard('admin')->user()->name ?? 'Administrator'),
            ]);

            $item->update(['status' => 'borrowed']);

            $item->category?->update([
                'total_quantity' => $item->category->items()->count(),
                'available_quantity' => $item->category->items()->where('status', 'available')->count(),
            ]);
        }

        ActivityLog::create([
            'clinic_id' => currentClinicId(),
            'actor_id' => Auth::guard('admin')->id(),
            'actor_type' => Auth::guard('admin')->user()::class,
            'action' => 'borrow.walk_in_created',
            'description' => 'Created walk-in borrow for user #'.$user->id,
            'properties' => [
                'borrower_user_id' => $user->id,
                'item_ids' => $items->pluck('id')->all(),
                'due_date' => $this->dueDate,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);

        $this->selectedUserId = null;
        $this->cartItemIds = [];
        $this->reason = '';
        $this->dueDate = now()->addDays(7)->toDateString();

        session()->flash('message', 'บันทึกรายการ walk-in borrow เรียบร้อยแล้ว');
    }

    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.walk-in-borrow-manager', [
                'tablesReady' => false,
                'matchedUsers' => collect(),
                'selectedUser' => null,
                'availableItems' => collect(),
                'cartItems' => collect(),
                'allCategories' => collect(),
            ]);
        }

        try {
            $matchedUsers = $this->userSearch === ''
                ? collect()
                : User::query()
                    ->with('primaryIdentity')
                    ->where(function ($query) {
                        $term = '%'.$this->userSearch.'%';
                        $query
                            ->where('full_name', 'like', $term)
                            ->orWhere('student_personnel_id', 'like', $term)
                            ->orWhere('citizen_id', 'like', $term)
                            ->orWhereHas('identities', fn ($identityQuery) => $identityQuery->where('identity_value', 'like', $term));
                    })
                    ->orderBy('full_name')
                    ->limit(8)
                    ->get();

            $selectedUser = $this->selectedUserId
                ? User::with('primaryIdentity')->find($this->selectedUserId)
                : null;

            $availableItems = BorrowItem::query()
                ->with('category')
                ->where('status', 'available')
                ->whereNotIn('id', $this->cartItemIds)
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
                ->orderBy('name')
                ->limit(40)
                ->get();

            $cartItems = BorrowItem::with('category')
                ->whereIn('id', $this->cartItemIds)
                ->get()
                ->sortBy(fn (BorrowItem $item) => array_search($item->id, $this->cartItemIds, true))
                ->values();

            return view('livewire.admin.walk-in-borrow-manager', [
                'tablesReady' => true,
                'matchedUsers' => $matchedUsers,
                'selectedUser' => $selectedUser,
                'availableItems' => $availableItems,
                'cartItems' => $cartItems,
                'allCategories' => BorrowCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            ]);
        } catch (QueryException) {
            return view('livewire.admin.walk-in-borrow-manager', [
                'tablesReady' => false,
                'matchedUsers' => collect(),
                'selectedUser' => null,
                'availableItems' => collect(),
                'cartItems' => collect(),
                'allCategories' => collect(),
            ]);
        }
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_records', 'borrow_items', 'borrow_categories', 'users'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
