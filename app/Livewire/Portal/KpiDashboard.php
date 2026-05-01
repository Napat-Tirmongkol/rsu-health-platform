<?php

namespace App\Livewire\Portal;

use App\Models\BorrowRecord;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\SatisfactionSurvey;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class KpiDashboard extends Component
{
    public int $commentPage = 1;

    private const COMMENTS_PER_PAGE = 5;

    public function render()
    {
        $comments = $this->surveyComments();

        return view('livewire.portal.kpi-dashboard', [
            'survey'     => $this->surveyKpi(),
            'comments'   => $comments,
            'campaign'   => $this->campaignKpi(),
            'users'      => $this->userKpi(),
            'borrow'     => $this->borrowKpi(),
            'updatedAt'  => now()->format('d/m/Y H:i'),
        ]);
    }

    public function prevCommentPage(): void
    {
        if ($this->commentPage > 1) {
            $this->commentPage--;
        }
    }

    public function nextCommentPage(int $totalPages): void
    {
        if ($this->commentPage < $totalPages) {
            $this->commentPage++;
        }
    }

    public function goToCommentPage(int $page): void
    {
        $this->commentPage = $page;
    }

    private function surveyKpi(): array
    {
        $rows = SatisfactionSurvey::withoutGlobalScopes()->get(['score', 'created_at']);

        $total     = $rows->count();
        $avg       = $total > 0 ? round($rows->avg('score'), 1) : null;
        $satRate   = $total > 0 ? (int) round($rows->where('score', '>=', 4)->count() / $total * 100) : 0;

        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();
        $lastStart = now()->subWeek()->startOfWeek();
        $lastEnd   = now()->subWeek()->endOfWeek();

        $thisWeek = $rows->filter(fn ($r) => Carbon::parse($r->created_at)->between($weekStart, $weekEnd))->count();
        $lastWeek = $rows->filter(fn ($r) => Carbon::parse($r->created_at)->between($lastStart, $lastEnd))->count();

        $dist = [];
        for ($i = 1; $i <= 5; $i++) {
            $dist[$i] = $rows->where('score', $i)->count();
        }

        return compact('total', 'avg', 'satRate', 'thisWeek', 'lastWeek', 'dist');
    }

    private function surveyComments(): array
    {
        $query = SatisfactionSurvey::withoutGlobalScopes()
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->orderByDesc('created_at')
            ->select(['score', 'comment', 'created_at']);

        $total      = $query->count();
        $totalPages = (int) ceil($total / self::COMMENTS_PER_PAGE);
        $page       = min(max(1, $this->commentPage), max(1, $totalPages));

        $items = $query->skip(($page - 1) * self::COMMENTS_PER_PAGE)
            ->take(self::COMMENTS_PER_PAGE)
            ->get();

        return compact('items', 'total', 'page', 'totalPages');
    }

    private function campaignKpi(): array
    {
        $active     = Campaign::withoutGlobalScopes()->where('status', 'active')->count();
        $totalQuota = (int) Campaign::withoutGlobalScopes()->where('status', 'active')->sum('total_capacity');
        $usedQuota  = Booking::withoutGlobalScopes()->whereIn('status', ['booked', 'confirmed'])->count();

        $bookingRate = $totalQuota > 0 ? (int) round($usedQuota / $totalQuota * 100) : 0;

        $done      = Booking::withoutGlobalScopes()->whereIn('status', ['completed', 'cancelled'])->count();
        $completed = Booking::withoutGlobalScopes()->where('status', 'completed')->count();

        $completionRate = $done > 0 ? (int) round($completed / $done * 100) : 0;

        return compact('active', 'totalQuota', 'usedQuota', 'bookingRate', 'completionRate');
    }

    private function userKpi(): array
    {
        $total = User::withoutGlobalScopes()->count();

        $lastMonthDate = now()->subMonthNoOverflow();

        $thisMonth = User::withoutGlobalScopes()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonth = User::withoutGlobalScopes()
            ->whereYear('created_at', $lastMonthDate->year)
            ->whereMonth('created_at', $lastMonthDate->month)
            ->count();

        $growth = $lastMonth > 0 ? (int) round(($thisMonth - $lastMonth) / $lastMonth * 100) : null;

        return compact('total', 'thisMonth', 'lastMonth', 'growth');
    }

    private function borrowKpi(): array
    {
        $total   = BorrowRecord::withoutGlobalScopes()->count();
        $active  = BorrowRecord::withoutGlobalScopes()->whereIn('status', ['borrowed', 'approved'])->count();
        $overdue = BorrowRecord::withoutGlobalScopes()
            ->where('status', 'borrowed')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        return compact('total', 'active', 'overdue');
    }
}
