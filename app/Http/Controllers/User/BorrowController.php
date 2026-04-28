<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use App\Models\BorrowRecord;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BorrowController extends Controller
{
    public function index()
    {
        if (! $this->borrowTablesReady(['borrow_categories', 'borrow_items'])) {
            return view('user.borrow.index', [
                'categories' => $this->emptyPaginator(),
            ])->with('message', 'ระบบยืมอุปกรณ์กำลังเตรียมข้อมูล กรุณารัน migration เพื่อเปิดใช้งานเต็มรูปแบบ');
        }

        try {
            $categories = BorrowCategory::query()
                ->where('is_active', true)
                ->withCount([
                    'items as available_items_count' => fn ($query) => $query->where('status', 'available'),
                ])
                ->orderBy('name')
                ->paginate(12);
        } catch (QueryException) {
            $categories = $this->emptyPaginator();
        }

        return view('user.borrow.index', [
            'categories' => $categories,
        ]);
    }

    public function create(BorrowCategory $category)
    {
        abort_unless($category->is_active, 404);

        $availableItems = BorrowItem::query()
            ->where('category_id', $category->id)
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        return view('user.borrow.request', [
            'category' => $category,
            'availableItems' => $availableItems,
        ]);
    }

    public function store(Request $request, BorrowCategory $category)
    {
        abort_unless($category->is_active, 404);

        if (! $this->borrowTablesReady(['borrow_items', 'borrow_records'])) {
            return redirect()
                ->route('user.borrow.index')
                ->withErrors([
                    'borrow' => 'ระบบยืมอุปกรณ์ยังไม่พร้อมบันทึกข้อมูล กรุณารัน migration ก่อนใช้งาน',
                ]);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1'],
        ]);

        $availableItem = BorrowItem::query()
            ->where('category_id', $category->id)
            ->where('status', 'available')
            ->orderBy('id')
            ->first();

        if (! $availableItem) {
            return back()
                ->withInput()
                ->withErrors([
                    'category' => 'อุปกรณ์ในหมวดนี้ยังไม่พร้อมให้ยืมในขณะนี้',
                ]);
        }

        BorrowRecord::create([
            'clinic_id' => currentClinicId(),
            'category_id' => $category->id,
            'borrower_user_id' => Auth::guard('user')->id(),
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'borrowed_at' => now(),
            'due_date' => $validated['due_date'] ?: null,
            'approval_status' => 'pending',
            'status' => 'borrowed',
            'fine_status' => 'none',
        ]);

        return redirect()
            ->route('user.borrow.history')
            ->with('message', 'ส่งคำขอยืมอุปกรณ์เรียบร้อยแล้ว');
    }

    public function history()
    {
        if (! $this->borrowTablesReady(['borrow_records'])) {
            return view('user.borrow.history', [
                'records' => $this->emptyPaginator(),
                'stats' => ['pending' => 0, 'active' => 0, 'returned' => 0],
            ])->with('message', 'ระบบยืมอุปกรณ์กำลังเตรียมข้อมูล กรุณารัน migration เพื่อเปิดใช้งานเต็มรูปแบบ');
        }

        try {
            $records = BorrowRecord::query()
                ->where('borrower_user_id', Auth::guard('user')->id())
                ->with(['category', 'item', 'fines.payments'])
                ->orderByDesc('created_at')
                ->paginate(20);

            $stats = [
                'pending' => BorrowRecord::query()
                    ->where('borrower_user_id', Auth::guard('user')->id())
                    ->where('approval_status', 'pending')
                    ->count(),
                'active' => BorrowRecord::query()
                    ->where('borrower_user_id', Auth::guard('user')->id())
                    ->where('approval_status', 'approved')
                    ->where('status', 'borrowed')
                    ->count(),
                'returned' => BorrowRecord::query()
                    ->where('borrower_user_id', Auth::guard('user')->id())
                    ->where('status', 'returned')
                    ->count(),
            ];
        } catch (QueryException) {
            $records = $this->emptyPaginator();
            $stats = ['pending' => 0, 'active' => 0, 'returned' => 0];
        }

        return view('user.borrow.history', [
            'records' => $records,
            'stats' => $stats,
        ]);
    }

    private function borrowTablesReady(array $tables): bool
    {
        foreach ($tables as $table) {
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
}
