<?php

namespace App\Livewire\Admin;

use App\Imports\InsuranceMemberImport;
use App\Imports\InsurancePolicyImport;
use App\Models\InsuranceMember;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class InsuranceMemberManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterType = '';
    public $filterMemberStatus = '';
    public $filterInsuranceStatus = '';

    public $memberFile;
    public $policyFile;
    public $importMemberType = 'student';
    public $importMemberStatus = 'active';

    public $showImportMemberModal = false;
    public $showImportPolicyModal = false;
    public $showDetailModal = false;
    public $selectedMember = null;

    public $importResult = null;

    protected $queryString = ['search', 'filterType', 'filterMemberStatus', 'filterInsuranceStatus'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterMemberStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterInsuranceStatus()
    {
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->selectedMember = InsuranceMember::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedMember = null;
    }

    public function importMembers(): void
    {
        $this->validate([
            'memberFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'importMemberType' => 'required|in:student,staff',
            'importMemberStatus' => 'required|in:active,resigned,inactive',
        ]);

        try {
            $clinicId = auth()->guard('admin')->user()->clinic_id ?? 1;

            Excel::import(
                new InsuranceMemberImport($clinicId, $this->importMemberType, $this->importMemberStatus),
                $this->memberFile->getRealPath()
            );

            $this->importResult = ['type' => 'success', 'message' => 'นำเข้าข้อมูลสมาชิกสำเร็จ'];
            $this->showImportMemberModal = false;
            $this->reset(['memberFile', 'importMemberType', 'importMemberStatus']);
            $this->importMemberType = 'student';
            $this->importMemberStatus = 'active';
        } catch (\Throwable $e) {
            $this->importResult = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    public function importPolicies(): void
    {
        $this->validate([
            'policyFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $clinicId = auth()->guard('admin')->user()->clinic_id ?? 1;

            Excel::import(
                new InsurancePolicyImport($clinicId),
                $this->policyFile->getRealPath()
            );

            $this->importResult = ['type' => 'success', 'message' => 'นำเข้าเลขกรมธรรม์สำเร็จ'];
            $this->showImportPolicyModal = false;
            $this->reset('policyFile');
        } catch (\Throwable $e) {
            $this->importResult = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    public function render()
    {
        $members = InsuranceMember::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('member_id', 'like', '%' . $this->search . '%')
                        ->orWhere('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('national_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType, fn ($q) => $q->where('member_type', $this->filterType))
            ->when($this->filterMemberStatus, fn ($q) => $q->where('member_status', $this->filterMemberStatus))
            ->when($this->filterInsuranceStatus, fn ($q) => $q->where('insurance_status', $this->filterInsuranceStatus))
            ->orderBy('member_type')
            ->orderBy('member_id')
            ->paginate(20);

        $stats = [
            'total'    => InsuranceMember::count(),
            'active'   => InsuranceMember::where('member_status', 'active')->count(),
            'covered'  => InsuranceMember::where('insurance_status', 'active')->count(),
            'pending'  => InsuranceMember::where('insurance_status', 'pending')->count(),
        ];

        return view('livewire.admin.insurance-member-manager', compact('members', 'stats'));
    }
}
