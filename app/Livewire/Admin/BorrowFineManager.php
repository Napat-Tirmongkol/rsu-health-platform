<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BorrowFine;
use App\Models\BorrowPayment;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowFineManager extends Component
{
    use WithPagination;

    public string $fineSearch = '';
    public string $paymentSearch = '';
    public bool $showPaymentModal = false;
    public ?BorrowFine $selectedFine = null;
    public string $amountPaid = '';
    public string $paymentMethod = 'cash';
    public string $paymentNotes = '';

    public function updatedFineSearch(): void
    {
        $this->resetPage('finesPage');
    }

    public function updatedPaymentSearch(): void
    {
        $this->resetPage('paymentsPage');
    }

    public function openPaymentModal(int $fineId): void
    {
        $this->authorizeAction('borrow.fine.collect');

        if (! $this->tablesReady()) {
            return;
        }

        $this->selectedFine = BorrowFine::with(['record.borrower.primaryIdentity', 'record.item', 'record.category', 'payments'])->findOrFail($fineId);
        $this->amountPaid = (string) number_format((float) $this->selectedFine->amount, 2, '.', '');
        $this->paymentMethod = 'cash';
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedFine = null;
        $this->amountPaid = '';
        $this->paymentMethod = 'cash';
        $this->paymentNotes = '';
    }

    public function recordPayment(): void
    {
        $this->authorizeAction('borrow.fine.collect');

        if (! $this->selectedFine) {
            return;
        }

        if (! $this->tablesReady()) {
            session()->flash('message', 'ระบบจัดการค่าปรับยังไม่พร้อมใช้งานเต็มรูปแบบ กรุณารัน migration ก่อน');
            return;
        }

        $this->validate([
            'amountPaid' => ['required', 'numeric', 'min:0.01'],
            'paymentMethod' => ['required', 'in:cash,bank_transfer'],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $fine = BorrowFine::with('record')->findOrFail($this->selectedFine->id);

        if ($fine->status === 'paid') {
            session()->flash('message', 'รายการค่าปรับนี้ถูกชำระแล้ว');
            $this->closePaymentModal();
            return;
        }

        if ((float) $this->amountPaid < (float) $fine->amount) {
            $this->addError('amountPaid', 'ยอดชำระต้องไม่น้อยกว่ายอดค่าปรับ');
            return;
        }

        $payment = BorrowPayment::create([
            'clinic_id' => $fine->clinic_id,
            'fine_id' => $fine->id,
            'amount_paid' => $this->amountPaid,
            'payment_method' => $this->paymentMethod,
            'payment_date' => now(),
            'received_by_staff_id' => null,
            'receipt_number' => 'FINE-'.str_pad((string) $fine->id, 6, '0', STR_PAD_LEFT),
            'payment_notes' => $this->paymentNotes ?: null,
        ]);

        $fine->update([
            'status' => 'paid',
            'notes' => trim(implode("\n", array_filter([
                $fine->notes,
                '['.now()->format('Y-m-d H:i').'] Payment recorded by admin '.(Auth::guard('admin')->user()->name ?? 'Administrator'),
                $this->paymentNotes ?: null,
            ]))),
        ]);

        if ($fine->record) {
            $fine->record->update(['fine_status' => 'paid']);
        }

        $admin = Auth::guard('admin')->user();

        ActivityLog::create([
            'clinic_id' => $fine->clinic_id,
            'actor_id' => $admin?->id,
            'actor_type' => $admin ? $admin::class : null,
            'action' => 'borrow.fine_paid',
            'description' => 'Recorded fine payment #'.$payment->id,
            'properties' => [
                'fine_id' => $fine->id,
                'borrow_record_id' => $fine->borrow_record_id,
                'amount_paid' => (float) $payment->amount_paid,
                'payment_method' => $payment->payment_method,
                'receipt_number' => $payment->receipt_number,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);

        session()->flash('message', 'บันทึกการรับชำระค่าปรับเรียบร้อยแล้ว');
        $this->closePaymentModal();
    }

    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.borrow-fine-manager', [
                'pendingFines' => $this->emptyPaginator('finesPage'),
                'paymentHistory' => $this->emptyPaginator('paymentsPage'),
                'stats' => ['pending' => 0, 'paid' => 0, 'pending_amount' => 0, 'collected_amount' => 0],
                'tablesReady' => false,
            ]);
        }

        try {
            $pendingFinesQuery = BorrowFine::with(['record.borrower.primaryIdentity', 'record.item', 'record.category'])
                ->where('status', 'pending')
                ->when($this->fineSearch, function ($query) {
                    $term = '%'.$this->fineSearch.'%';

                    return $query->where(function ($fineQuery) use ($term) {
                        $fineQuery
                            ->where('notes', 'like', $term)
                            ->orWhereHas('record.borrower', function ($userQuery) use ($term) {
                                $userQuery
                                    ->where('full_name', 'like', $term)
                                    ->orWhere('student_personnel_id', 'like', $term)
                                    ->orWhere('citizen_id', 'like', $term);
                            })
                            ->orWhereHas('record.borrower.identities', fn ($identityQuery) => $identityQuery->where('identity_value', 'like', $term))
                            ->orWhereHas('record.item', function ($itemQuery) use ($term) {
                                $itemQuery
                                    ->where('name', 'like', $term)
                                    ->orWhere('serial_number', 'like', $term);
                            })
                            ->orWhereHas('record.category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $term));
                    });
                })
                ->latest();

            $paymentHistoryQuery = BorrowPayment::with(['fine.record.borrower.primaryIdentity', 'fine.record.item', 'fine.record.category'])
                ->when($this->paymentSearch, function ($query) {
                    $term = '%'.$this->paymentSearch.'%';

                    return $query->where(function ($paymentQuery) use ($term) {
                        $paymentQuery
                            ->where('receipt_number', 'like', $term)
                            ->orWhere('payment_method', 'like', $term)
                            ->orWhereHas('fine.record.borrower', function ($userQuery) use ($term) {
                                $userQuery
                                    ->where('full_name', 'like', $term)
                                    ->orWhere('student_personnel_id', 'like', $term)
                                    ->orWhere('citizen_id', 'like', $term);
                            })
                            ->orWhereHas('fine.record.item', function ($itemQuery) use ($term) {
                                $itemQuery
                                    ->where('name', 'like', $term)
                                    ->orWhere('serial_number', 'like', $term);
                            })
                            ->orWhereHas('fine.record.category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', $term));
                    });
                })
                ->latest('payment_date');

            $stats = [
                'pending' => BorrowFine::where('status', 'pending')->count(),
                'paid' => BorrowFine::where('status', 'paid')->count(),
                'pending_amount' => (float) BorrowFine::where('status', 'pending')->sum('amount'),
                'collected_amount' => (float) BorrowPayment::sum('amount_paid'),
            ];

            return view('livewire.admin.borrow-fine-manager', [
                'pendingFines' => $pendingFinesQuery->paginate(20, ['*'], 'finesPage'),
                'paymentHistory' => $paymentHistoryQuery->paginate(20, ['*'], 'paymentsPage'),
                'stats' => $stats,
                'tablesReady' => true,
            ]);
        } catch (QueryException) {
            return view('livewire.admin.borrow-fine-manager', [
                'pendingFines' => $this->emptyPaginator('finesPage'),
                'paymentHistory' => $this->emptyPaginator('paymentsPage'),
                'stats' => ['pending' => 0, 'paid' => 0, 'pending_amount' => 0, 'collected_amount' => 0],
                'tablesReady' => false,
            ]);
        }
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_fines', 'borrow_payments', 'borrow_records'] as $table) {
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

    private function authorizeAction(string $action): void
    {
        abort_unless(Auth::guard('admin')->user()?->hasActionAccess($action), 403);
    }
}
