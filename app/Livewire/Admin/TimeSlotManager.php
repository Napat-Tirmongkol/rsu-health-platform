<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\Slot;
use Carbon\Carbon;
use Livewire\Component;

class TimeSlotManager extends Component
{
    public $currentMonth;
    public $currentYear;
    
    // View state
    public $viewMode = 'calendar'; // calendar or table
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $camp_id, $selected_dates = [], $start_times = [''], $end_times = [''], $max_slots = 50;
    
    // Filters
    public $filterCampId = 'all';

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function prevMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function openAddModal($date = null)
    {
        $this->reset(['editingId', 'camp_id', 'selected_dates', 'start_times', 'end_times']);
        $this->start_times = ['09:00'];
        $this->end_times = ['10:00'];
        if ($date) {
            $this->selected_dates = [$date];
        }
        $this->showModal = true;
    }

    public function addTimeRow()
    {
        $this->start_times[] = '';
        $this->end_times[] = '';
    }

    public function removeTimeRow($index)
    {
        unset($this->start_times[$index]);
        unset($this->end_times[$index]);
        $this->start_times = array_values($this->start_times);
        $this->end_times = array_values($this->end_times);
    }

    public function save()
    {
        $this->validate([
            'camp_id' => 'required',
            'selected_dates' => 'required|array|min:1',
            'start_times.*' => 'required',
            'end_times.*' => 'required',
            'max_slots' => 'required|integer|min:1',
        ]);

        $valid_time_count = count(array_filter($this->start_times));
        if ($valid_time_count === 0) return;

        $base_cap = floor($this->max_slots / $valid_time_count);
        $remainder = $this->max_slots % $valid_time_count;

        foreach ($this->selected_dates as $date) {
            $temp_remainder = $remainder;
            for ($i = 0; $i < count($this->start_times); $i++) {
                if (empty($this->start_times[$i]) || empty($this->end_times[$i])) continue;

                $cap = $base_cap + ($temp_remainder > 0 ? 1 : 0);
                $temp_remainder--;

                Slot::create([
                    'camp_id' => $this->camp_id,
                    'date' => $date,
                    'start_time' => $this->start_times[$i],
                    'end_time' => $this->end_times[$i],
                    'max_slots' => $cap,
                    'status' => 'available',
                ]);
            }
        }

        $this->showModal = false;
        session()->flash('message', 'สร้างรอบเวลาเรียบร้อยแล้ว');
    }

    public function deleteSlot($id)
    {
        $slot = Slot::findOrFail($id);
        
        if ($slot->bookings()->whereIn('status', ['pending', 'confirmed'])->count() > 0) {
            // In a real app, we'd send notifications here. 
            // For now, let's update status or show a warning.
            session()->flash('error', 'ไม่สามารถลบได้เนื่องจากมีผู้จองแล้ว (ระบบแจ้งเตือนอัตโนมัติกำลังถูกพัฒนา)');
            return;
        }

        $slot->delete();
        session()->flash('message', 'ลบรอบเวลาเรียบร้อยแล้ว');
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth();
        
        $calendarDays = [];
        $date = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $end = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        while ($date <= $end) {
            $calendarDays[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === $this->currentMonth,
                'slots' => Slot::with('campaign')
                    ->withCount(['bookings' => fn($q) => $q->whereIn('status', ['pending', 'confirmed'])])
                    ->where('date', $date->format('Y-m-d'))
                    ->when($this->filterCampId !== 'all', fn($q) => $q->where('camp_id', $this->filterCampId))
                    ->orderBy('start_time')
                    ->get()
            ];
            $date->addDay();
        }

        return view('livewire.admin.time-slot-manager', [
            'calendarDays' => $calendarDays,
            'campaigns' => Campaign::where('status', 'active')->get(),
            'monthName' => $startOfMonth->locale('th')->translatedFormat('F Y')
        ]);
    }
}
