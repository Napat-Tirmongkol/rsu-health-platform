<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowPayment;

class BorrowReceiptController extends Controller
{
    public function show(BorrowPayment $payment)
    {
        $payment->load([
            'fine.record.borrower.primaryIdentity',
            'fine.record.item',
            'fine.record.category',
        ]);

        return view('admin.borrow_receipt', [
            'payment' => $payment,
            'fine' => $payment->fine,
            'record' => $payment->fine?->record,
            'borrower' => $payment->fine?->record?->borrower,
            'item' => $payment->fine?->record?->item,
            'category' => $payment->fine?->record?->category,
        ]);
    }
}
