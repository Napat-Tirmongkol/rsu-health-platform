<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfileEdit extends Component
{
    private const PREFIX_OPTIONS = [
        'นาย',
        'นาง',
        'นางสาว',
        'นพ.',
        'พญ.',
        'ทพ.',
        'ทญ.',
        'ภก.',
        'ภญ.',
        'พย.',
        'ดร.',
        'อ.',
        'ผศ.',
        'รศ.',
        'ศ.',
    ];

    public $prefix;
    public $custom_prefix;
    public $first_name;
    public $last_name;
    public $gender;
    public $status;
    public $id_type = 'citizen';
    public $citizen_id;
    public $student_id;
    public $department;
    public $phone_number;
    public $email;
    public $agreed = false;

    public $faculties = [];

    public function mount()
    {
        $user = Auth::guard('user')->user();

        $this->prefix = in_array($user->prefix, self::PREFIX_OPTIONS, true) ? $user->prefix : ($user->prefix ? 'other' : '');
        $this->custom_prefix = $this->prefix === 'other' ? $user->prefix : '';
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->gender = $user->gender ?? 'male';
        $this->status = $user->status ?? 'student';
        $this->citizen_id = $user->citizen_id;
        $this->student_id = $user->student_personnel_id;
        $this->department = $user->department;
        $this->phone_number = $user->phone_number;
        $this->email = $user->email;
        $this->agreed = true;

        if ($this->citizen_id && (! ctype_digit($this->citizen_id) || strlen($this->citizen_id) !== 13)) {
            $this->id_type = 'passport';
        }

        $this->faculties = DB::table('sys_faculties')->orderBy('name_th')->pluck('name_th')->toArray();
    }

    public function updatedPrefix($value)
    {
        if ($value !== 'other') {
            $this->custom_prefix = '';
        }
    }

    public function save()
    {
        $this->validate([
            'prefix' => 'required',
            'custom_prefix' => 'required_if:prefix,other',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'status' => 'required|in:student,staff,other',
            'citizen_id' => 'required|string',
            'student_id' => 'required_unless:status,other',
            'department' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'nullable|email',
            'agreed' => 'accepted',
        ]);

        $user = User::find(Auth::guard('user')->id());
        $finalPrefix = $this->prefix === 'other' ? $this->custom_prefix : $this->prefix;

        $user->update([
            'prefix' => $finalPrefix,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'gender' => $this->gender,
            'status' => $this->status,
            'citizen_id' => $this->citizen_id,
            'student_personnel_id' => $this->student_id,
            'department' => $this->department,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
        ]);

        $this->dispatch('swal:success', message: 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว');
    }

    public function render()
    {
        return view('livewire.user.profile-edit');
    }
}
