<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BorrowFine;
use App\Models\BorrowPayment;
use App\Models\BorrowRecord;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowReturnManager extends Component
{
    use WithPagination;

    private const FINE_RATE_PER_DAY = 10;

    public string $search = '';
    public string $filter = 'all';
    public bool $showReturnModal = false;
    public ?BorrowRecord $selectedRecord = null;
    public string $returnNotes = '';
    public bool $collectFineNow = false;
    public string $paymentMethod = 'cash';
    public string $amountPaid = '';
    public string $paymentNotes = '';

    protected $queryString = ['filter', 'search'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function openReturnModal(int $recordId): void
    {
        if (! $this->tablesReady()) {
            return;
        }

        $this->selectedRecord = BorrowRecord::with(['borrower.primaryIdentity', 'category', 'item', 'fines.payments'])->findOrFail($recordId);
        $this->returnNotes = '';
        $this->paymentNotes = '';
        $this->paymentMethod = 'cash';
        $this->collectFineNow = false;
        $this->amountPaid = (string) number_format($this->calculatedFineAmount($this->selectedRecord), 2, '.', '');
        $this->showReturnModal = true;
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->selectedRecord = null;
        $this->returnNotes = '';
        $this->collectFineNow = false;
        $this->paymentMethod = 'cash';
        $this->amountPaid = '';
        $this->paymentNotes = '';
    }

    public function processReturn(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบคืนอุปกรณ์ยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');
            return;
        }

        $record = BorrowRecord::with(['item', 'borrower', 'category', 'fines.payments'])->findOrFail($this->selectedRecord->id);

        if ($record->status === 'returned') {
            session()->flash('message', 'รายการนี้ถูกคืนแล้ว');
            $this->closeReturnModal();
            return;
        }

        $fineAmount = $this->calculatedFineAmount($record);
        $hasOverdueFine = $fineAmount > 0;

        $this->validate([
            'returnNotes' => ['nullable', 'string', 'max:1000'],
            'collectFineNow' => ['boolean'],
            'paymentMethod' => ['required_if:collectFineNow,true', 'in:cash,bank_transfer'],
            'amountPaid' => ['nullable', 'numeric', 'min:0'],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($hasOverdueFine && $this->collectFineNow && (float) $this->amountPaid < $fineAmount) {
            $this->addError('amountPaid', 'ยอดชำระต้องไม่น้อยกว่าค่าปรับที่คำนวณได้');
            return;
        }

        $record->update([
            'status' => 'returned',
            'returned_at' => now(),
            'fine_status' => $hasOverdueFine ? ($this->collectFineNow ? 'paid' : 'pending') : 'none',
            'notes' => $this->appendAuditNote($record->notes, $this->returnNotes),
        ]);

        if ($record->item) {
            $record->item->update(['status' => 'available']);
            $record->item->category?->update([
                'total_quantity' => $record->item->category->items()->count(),
                'available_quantity' => $record->item->category->items()->where('status', 'available')->count(),
            ]);
        }

        if ($hasOverdueFine) {
            $fine = BorrowFine::firstOrCreate(
                ['borrow_record_id' => $record->id],
                [
                    'clinic_id' => $record->clinic_id,
                    'user_id' => $record->borrower_user_id,
                    'amount' => $fineAmount,
                    'status' => $this->collectFineNow ? 'paid' : 'pending',
                    'notes' => 'Late return fine',
                    'created_by_staff_id' => null,
                ]
            );

            $fine->update([
                'amount' => $fineAmount,
                'status' => $this->collectFineNow ? 'paid' : 'pending',
                'notes' => trim(implode("\n", array_filter([
                    $fine->notes,
                    $this->paymentNotes ?: null,
                ]))),
            ]);

            if ($this->collectFineNow) {
                BorrowPayment::create([
                    'clinic_id' => $record->clinic_id,
                    'fine_id' => $fine->id,
                    'amount_paid' => $this->amountPaid,
                    'payment_method' => $this->paymentMethod,
                    'payment_date' => now(),
                    'received_by_staff_id' => null,
                    'receipt_number' => 'BORROW-'.str_pad((string) $record->id, 6, '0', STR_PAD_LEFT),
                    'payment_notes' => $this->paymentNotes ?: null,
                ]);
            }
        }

        $this->logAction($record, $fineAmount, $hasOverdueFine);

        session()->flash('message', $hasOverdueFine
            ? ($this->collectFineNow ? 'รับคืนอุปกรณ์และบันทึกการชำระค่าปรับเรียบร้อยแล้ว' : 'รับคืนอุปกรณ์และสร้างค่าปรับค้างชำระเรียบร้อยแล้ว')
            : 'รับคืนอุปกรณ์เรียบร้อยแล้ว');

        $this->closeReturnModal();
    }

    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.borrow-return-manager', [
                'records' => $this->emptyPaginator(),
                'stats' => ['borrowed' => 0, 'overdue' => 0, 'fine_pending' => 0, 'returned_today' => 0],
                'tablesReady' => false,
            ]);
        }

        try {
            $query = BorrowRecord::with(['borrower.primaryIdentity', 'category', 'item', 'fines'])
                ->where('status', 'borrowed')
                ->whereIn('approval_status', ['approved', 'staff_added'])
                ->when($this->filter === 'overdue', fn ($q) => $q->whereDate('due_date', '<', today()))
                ->when($this->filter === 'due_today', fn ($q) => $q->whereDate('due_date', today()))
                ->when($this->filter === 'fine_pending', fn ($q) => $q->where('fine_status', 'pending'))
                ->when($this->search, function ($q) {
                    $term = '%'.$this->search.'%';

                    return $q->where(function ($recordQuery) use ($term) {
                        $recordQuery
                            ->whereHas('borrower', function ($userQuery) use ($term) {
                                $userQuery
                                    ->where('full_name', 'like', $term)
                                    ->orWhere('student_personnel_id', 'like', $term)
                                    ->orWhere('citizen_id', 'like', $term);
                            })
                            ->orWhereHas('borrower.identities', fn ($identityQuery) => $identityQuery->where('identity_value', 'like', $term))
                            ->orWhereHas('item', function ($itemQuery) use ($term) {
                                $itemQuery
                                    ->where('name', 'like', $term)
                                    ->orWhere('serial_number', 'like', $term);
                            })
                            ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $term));
                    });
                });

            $stats = [
                'borrowed' => BorrowRecord::where('status', 'borrowed')->whereIn('approval_status', ['approved', 'staff_added'])->count(),
                'overdue' => BorrowRecord::where('status', 'borrowed')->whereIn('approval_status', ['approved', 'staff_added'])->whereDate('due_date', '<', today())->count(),
                'fine_pending' => BorrowRecord::where('status', 'borrowed')->where('fine_status', 'pending')->count(),
                'returned_today' => BorrowRecord::where('status', 'returned')->whereDate('returned_at', today())->count(),
            ];

            $records = $query->orderBy('due_date')->paginate(20);
        } catch (QueryException) {
            $stats = ['borrowed' => 0, 'overdue' => 0, 'fine_pending' => 0, 'returned_today' => 0];
            $records = $this->emptyPaginator();
        }

        return view('livewire.admin.borrow-return-manager', [
            'records' => $records,
            'stats' => $stats,
            'tablesReady' => true,
        ]);
    }

    public function overdueDays(BorrowRecord $record): int
    {
        if (! $record->due_date || $record->due_date->gte(today())) {
            return 0;
        }

        return $record->due_date->diffInDays(today());
    }

    public function calculatedFineAmount(BorrowRecord $record): float
    {
        return $this->overdueDays($record) * self::FINE_RATE_PER_DAY;
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_records', 'borrow_items', 'borrow_fines', 'borrow_payments'] as $table) {
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

    private function appendAuditNote(?string $existingNotes, string $returnNotes): string
    {
        $lines = [
            $existingNotes,
            '['.now()->format('Y-m-d H:i').'] Returned by admin '.(Auth::guard('admin')->user()->name ?? 'Administrator'),
        ];

        if (trim($returnNotes) !== '') {
            $lines[] = 'Note: '.trim($returnNotes);
        }

        return trim(implode("\n", array_filter($lines)));
    }

    private function logAction(BorrowRecord $record, float $fineAmount, bool $hasOverdueFine): void
    {
        $admin = Auth::guard('admin')->user();

        ActivityLog::create([
            'clinic_id' => $record->clinic_id,
            'actor_id' => $admin?->id,
            'actor_type' => $admin ? $admin::class : null,
            'action' => 'borrow.returned',
            'description' => 'Returned borrow record #'.$record->id,
            'properties' => [
                'borrow_record_id' => $record->id,
                'borrow_item_id' => $record->item_id,
                'borrower_user_id' => $record->borrower_user_id,
                'fine_amount' => $hasOverdueFine ? $fineAmount : 0,
                'fine_status' => $record->fine_status,
                'payment_method' => $this->collectFineNow ? $this->paymentMethod : null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);
    }
}
