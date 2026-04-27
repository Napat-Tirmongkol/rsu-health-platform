<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $title, $type = 'vaccine', $description, $total_capacity = 0, $status = 'active', $is_auto_approve = false, $starts_at, $ends_at;

    protected $rules = [
        'title' => 'required|min:3',
        'type' => 'required',
        'total_capacity' => 'required|integer|min:0',
        'status' => 'required',
        'is_auto_approve' => 'boolean',
        'starts_at' => 'nullable|date',
        'ends_at' => 'nullable|date|after_or_equal:starts_at',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->reset(['editingId', 'title', 'description', 'total_capacity', 'starts_at', 'ends_at']);
        $this->type = 'vaccine';
        $this->status = 'active';
        $this->is_auto_approve = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $campaign = Campaign::findOrFail($id);
        $this->editingId = $id;
        $this->title = $campaign->title;
        $this->type = $campaign->type;
        $this->description = $campaign->description;
        $this->total_capacity = $campaign->total_capacity;
        $this->status = $campaign->status;
        $this->is_auto_approve = $campaign->is_auto_approve;
        $this->starts_at = $campaign->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $campaign->ends_at?->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'total_capacity' => $this->total_capacity,
            'status' => $this->status,
            'is_auto_approve' => $this->is_auto_approve,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
        ];

        if ($this->editingId) {
            Campaign::findOrFail($this->editingId)->update($data);
            $message = 'อัปเดตแคมเปญเรียบร้อยแล้ว';
        } else {
            Campaign::create($data);
            $message = 'สร้างแคมเปญใหม่เรียบร้อยแล้ว';
        }

        $this->showModal = false;
        session()->flash('message', $message);
    }

    public function delete($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        if ($campaign->bookings()->count() > 0) {
            session()->flash('error', 'ไม่สามารถลบได้ เนื่องจากมีผู้ลงทะเบียนแล้ว (แนะนำให้ปิดสถานะแทน)');
            return;
        }

        $campaign->delete();
        session()->flash('message', 'ลบแคมเปญเรียบร้อยแล้ว');
    }

    public function generateNewToken($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update(['share_token' => bin2hex(random_bytes(8))]);
        session()->flash('message', 'รีเซ็ตลิงก์แชร์เรียบร้อยแล้ว');
    }

    public function render()
    {
        $campaigns = Campaign::where('title', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.campaign-manager', [
            'campaigns' => $campaigns
        ]);
    }
}
