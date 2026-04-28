<?php

namespace App\Livewire\Admin;

use App\Models\BorrowFine;
use App\Models\BorrowPayment;
use App\Models\BorrowRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class BorrowOperationsDashboard extends Component
{
    public function render()
    {
        if (! $this->tablesReady()) {
            return view('livewire.admin.borrow-operations-dashboard', [
                'tablesReady' => false,
                'stats' => [
                    'active_borrows' => 0,
                    'pending_requests' => 0,
                    'overdue_items' => 0,
                    'pending_fine_amount' => 0,
                    'collected_this_month' => 0,
                ],
                'recentTransactions' => collect(),
                'attentionItems' => collect(),
            ]);
        }

        try {
            $stats = [
                'active_borrows' => BorrowRecord::where('status', 'borrowed')
                    ->whereIn('approval_status', ['approved', 'staff_added'])
                    ->count(),
                'pending_requests' => BorrowRecord::where('approval_status', 'pending')->count(),
                'overdue_items' => BorrowRecord::where('status', 'borrowed')
                    ->whereIn('approval_status', ['approved', 'staff_added'])
                    ->whereDate('due_date', '<', today())
                    ->count(),
                'pending_fine_amount' => (float) BorrowFine::where('status', 'pending')->sum('amount'),
                'collected_this_month' => (float) BorrowPayment::whereBetween('payment_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])->sum('amount_paid'),
            ];

            $recentTransactions = BorrowRecord::with(['borrower.primaryIdentity', 'item', 'category'])
                ->whereIn('approval_status', ['approved', 'staff_added'])
                ->latest('borrowed_at')
                ->limit(6)
                ->get();

            $attentionItems = BorrowRecord::with(['borrower.primaryIdentity', 'item', 'category', 'fines'])
                ->where('status', 'borrowed')
                ->where(function ($query) {
                    $query
                        ->whereDate('due_date', '<', today())
                        ->orWhere('fine_status', 'pending');
                })
                ->orderBy('due_date')
                ->limit(6)
                ->get();
        } catch (QueryException) {
            $stats = [
                'active_borrows' => 0,
                'pending_requests' => 0,
                'overdue_items' => 0,
                'pending_fine_amount' => 0,
                'collected_this_month' => 0,
            ];
            $recentTransactions = collect();
            $attentionItems = collect();
        }

        return view('livewire.admin.borrow-operations-dashboard', [
            'tablesReady' => true,
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'attentionItems' => $attentionItems,
        ]);
    }

    public function overdueDays(BorrowRecord $record): int
    {
        if (! $record->due_date || $record->due_date->gte(today())) {
            return 0;
        }

        return $record->due_date->diffInDays(today());
    }

    private function tablesReady(): bool
    {
        foreach (['borrow_records', 'borrow_fines', 'borrow_payments'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
