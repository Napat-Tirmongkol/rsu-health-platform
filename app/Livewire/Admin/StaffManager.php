<?php

namespace App\Livewire\Admin;

use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class StaffManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $username, $full_name, $email, $password, $role = 'staff', $status = 'active';

    protected function rules()
    {
        return [
            'username' => 'required|min:3|unique:sys_staff,username,' . $this->editingId,
            'full_name' => 'required|min:3',
            'email' => 'required|email|unique:sys_staff,email,' . $this->editingId,
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required',
            'status' => 'required',
        ];
    }

    public function openAddModal()
    {
        $this->reset(['editingId', 'username', 'full_name', 'email', 'password']);
        $this->role = 'staff';
        $this->status = 'active';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);

        $this->editingId = $id;
        $this->username = $staff->username;
        $this->full_name = $staff->full_name;
        $this->email = $staff->email;
        $this->password = '';
        $this->role = $staff->role;
        $this->status = $staff->status;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'username' => $this->username,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            Staff::findOrFail($this->editingId)->update($data);
            $message = 'อัปเดตข้อมูลเจ้าหน้าที่เรียบร้อยแล้ว';
        } else {
            Staff::create($data);
            $message = 'เพิ่มเจ้าหน้าที่ใหม่เรียบร้อยแล้ว';
        }

        $this->showModal = false;
        session()->flash('message', $message);
    }

    public function toggleStatus($id)
    {
        $staff = Staff::findOrFail($id);

        $staff->update([
            'status' => $staff->status === 'active' ? 'disabled' : 'active',
        ]);

        session()->flash('message', 'อัปเดตสถานะเจ้าหน้าที่เรียบร้อยแล้ว');
    }

    public function delete($id)
    {
        if (auth('admin')->id() == $id) {
            session()->flash('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
            return;
        }

        Staff::findOrFail($id)->delete();
        session()->flash('message', 'ลบเจ้าหน้าที่เรียบร้อยแล้ว');
    }

    public function render()
    {
        $staffs = Staff::where(function ($q) {
            $q->where('full_name', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
        })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.staff-manager', [
            'staffs' => $staffs,
        ]);
    }
}
