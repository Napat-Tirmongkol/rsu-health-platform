<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BorrowItem;
use App\Models\BorrowRecord;
use App\Services\BorrowNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowRequestManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'pending';
    public bool $showDrawer = false;
    public ?BorrowRecord $selectedRecordDetails = null;

    protected $queryString = ['statusFilter', 'search'];

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openDetails(int $id): void
    {
        if (! $this->tablesReady()) {
            return;
        }

        $this->selectedRecordDetails = BorrowRecord::with([
            'borrower.primaryIdentity',
            'category',
            'item',
            'fines.payments',
        ])->findOrFail($id);

        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->selectedRecordDetails = null;
    }

    public function approve(int $id, BorrowNotificationService $notifications): void
    {
        $this->authorizeAction('borrow.request.approve');

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ e-Borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');

            return;
        }

        $record = BorrowRecord::with(['category', 'borrower', 'item'])->findOrFail($id);

        if ($record->approval_status !== 'pending') {
            session()->flash('message', 'รายการนี้ไม่ได้อยู่ในสถานะรออนุมัติแล้ว');

            return;
        }

        $item = $record->item ?: BorrowItem::query()
            ->where('category_id', $record->category_id)
            ->where('status', 'available')
            ->orderBy('id')
            ->first();

        if (! $item) {
            session()->flash('message', 'ยังไม่มีอุปกรณ์ว่างสำหรับคำขอนี้');

            return;
        }

        $item->update(['status' => 'borrowed']);

        $record->update([
            'item_id' => $item->id,
            'approval_status' => 'approved',
            'notes' => $this->appendAuditNote($record->notes, 'Approved by admin '.(Auth::guard('admin')->user()->name ?? 'Administrator')),
        ]);

        $this->logAction('borrow.approved', $record, [
            'borrow_item_id' => $item->id,
            'borrow_item_name' => $item->name,
        ]);

        $notifications->requestApproved($record->fresh(['borrower', 'category', 'item']));

        session()->flash('message', 'อนุมัติคำขอยืมเรียบร้อยแล้ว');
        $this->refreshDrawer($record->id);
    }

    public function reject(int $id): void
    {
        $this->authorizeAction('borrow.request.approve');

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบ e-Borrow ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');

            return;
        }

        $record = BorrowRecord::with(['category', 'borrower', 'item'])->findOrFail($id);

        if ($record->approval_status !== 'pending') {
            session()->flash('message', 'รายการนี้ไม่ได้อยู่ในสถานะรออนุมัติแล้ว');

            return;
        }

        if ($record->item && $record->item->status !== 'available') {
            $record->item->update(['status' => 'available']);
        }

        $record->update([
            'approval_status' => 'rejected',
            'notes' => $this->appendAuditNote($record->notes, 'Rejected by admin '.(Auth::guard('admin')->user()->name ?? 'Administrator')),
        ]);

        $this->logAction('borrow.rejected', $record);

        session()->flash('message', 'ปฏิเสธคำขอยืมเรียบร้อยแล้ว');
        $this->refreshDrawer($record->id);
    }

    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.borrow-request-manager', [
                'records' => $this->emptyPaginator(),
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'active' => 0],
                'tablesReady' => false,
            ]);
        }

        try {
            $query = BorrowRecord::with(['borrower.primaryIdentity', 'category', 'item'])
                ->when($this->statusFilter !== 'all', function ($q) {
                    return $q->where('approval_status', $this->statusFilter);
                })
                ->when($this->search, function ($q) {
                    $term = '%'.$this->search.'%';

                    return $q->where(function ($recordQuery) use ($term) {
                        $recordQuery
                            ->where('reason', 'like', $term)
                            ->orWhereHas('borrower', function ($userQuery) use ($term) {
                                $userQuery
                                    ->where('full_name', 'like', $term)
                                    ->orWhere('student_personnel_id', 'like', $term)
                                    ->orWhere('citizen_id', 'like', $term);
                            })
                            ->orWhereHas('borrower.identities', function ($identityQuery) use ($term) {
                                $identityQuery->where('identity_value', 'like', $term);
                            })
                            ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $term))
                            ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $term));
                    });
                });

            $stats = [
                'pending' => BorrowRecord::where('approval_status', 'pending')->count(),
                'approved' => BorrowRecord::where('approval_status', 'approved')->count(),
                'rejected' => BorrowRecord::where('approval_status', 'rejected')->count(),
                'active' => BorrowRecord::where('approval_status', 'approved')->where('status', 'borrowed')->count(),
            ];

            $records = $query->latest()->paginate(20);
        } catch (QueryException) {
            $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'active' => 0];
            $records = $this->emptyPaginator();
        }

        return view('livewire.admin.borrow-request-manager', [
            'records' => $records,
            'stats' => $stats,
            'tablesReady' => true,
        ]);
    }

    private function refreshDrawer(int $recordId): void
    {
        if ($this->showDrawer) {
            $this->selectedRecordDetails = BorrowRecord::with([
                'borrower.primaryIdentity',
                'category',
                'item',
                'fines.payments',
            ])->find($recordId);
        }
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_records', 'borrow_categories', 'borrow_items'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            new Collection(),
            0,
            20,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    private function logAction(string $action, BorrowRecord $record, array $extraProperties = []): void
    {
        $admin = Auth::guard('admin')->user();

        ActivityLog::create([
            'clinic_id' => $record->clinic_id,
            'actor_id' => $admin?->id,
            'actor_type' => $admin ? $admin::class : null,
            'action' => $action,
            'description' => ucfirst(str_replace('.', ' ', $action)).' record #'.$record->id,
            'properties' => array_merge([
                'borrow_record_id' => $record->id,
                'borrower_user_id' => $record->borrower_user_id,
                'category_id' => $record->category_id,
                'approval_status' => $record->approval_status,
            ], $extraProperties),
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);
    }

    private function appendAuditNote(?string $existingNotes, string $line): string
    {
        return trim(implode("\n", array_filter([
            $existingNotes,
            '['.now()->format('Y-m-d H:i').'] '.$line,
        ])));
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(Auth::guard('admin')->user()?->hasActionAccess($action), 403);
    }
}
