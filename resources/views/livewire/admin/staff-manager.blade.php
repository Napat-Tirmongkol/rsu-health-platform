<div class="space-y-8 animate-in fade-in duration-700">
    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">จัดการทีมเจ้าหน้าที่</h1>
            <p class="mt-1 text-sm font-bold uppercase tracking-widest text-slate-400">Staff & Team Management</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="group relative">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 transition-colors group-focus-within:text-blue-500"></i>
                <input
                    wire:model.live="search"
                    type="text"
                    placeholder="ค้นหาชื่อ, username หรืออีเมล..."
                    class="w-full rounded-2xl border border-slate-100 bg-white py-3.5 pl-11 pr-6 text-sm font-bold text-slate-700 shadow-sm transition-all focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-50 md:w-80"
                >
            </div>

            <button wire:click="openAddModal" class="flex items-center gap-3 rounded-2xl bg-slate-900 px-8 py-3.5 font-black text-white shadow-lg shadow-slate-200 transition-all hover:-translate-y-1 hover:shadow-slate-300 active:scale-95">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <span>เพิ่มเจ้าหน้าที่</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-600 animate-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-check text-lg"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm font-bold text-rose-600 animate-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($staffs as $s)
            <div class="group relative overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white p-8 shadow-sm transition-all hover:shadow-md">
                @if($s->status === 'disabled')
                    <div class="absolute inset-0 z-10 flex items-center justify-center bg-slate-50/60 backdrop-blur-[1px]">
                        <span class="rounded-full bg-rose-500 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white shadow-lg">Disabled Account</span>
                    </div>
                @endif

                <div class="mb-6 flex items-start justify-between">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl border border-slate-100 bg-slate-50 text-2xl text-slate-300 shadow-inner transition-transform group-hover:scale-110">
                        @php
                            $initials = mb_strtoupper(mb_substr($s->full_name ?: $s->username, 0, 1));
                        @endphp
                        <span class="font-black text-slate-400 transition-colors group-hover:text-blue-500">{{ $initials }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $s->id }})" class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-500 shadow-sm transition-all hover:bg-blue-500 hover:text-white">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button wire:click="toggleStatus({{ $s->id }})" class="flex h-10 w-10 items-center justify-center rounded-xl {{ $s->status === 'active' ? 'bg-emerald-50 text-emerald-500 hover:bg-emerald-500' : 'bg-rose-50 text-rose-500 hover:bg-rose-500' }} shadow-sm transition-all hover:text-white">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <h4 class="text-xl font-black tracking-tight text-slate-800">{{ $s->full_name }}</h4>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">@ {{ $s->username }}</p>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-slate-50 pt-6">
                    <div class="flex flex-col">
                        <span class="mb-1 text-[9px] font-black uppercase tracking-widest text-slate-300">บทบาท</span>
                        <span class="text-xs font-black uppercase tracking-widest text-slate-600">{{ $s->role }}</span>
                    </div>

                    <div class="text-right">
                        <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-slate-300">อีเมลติดต่อ</span>
                        <span class="text-xs font-bold text-slate-500">{{ $s->email }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[3rem] border border-dashed border-slate-100 bg-white py-32 text-center opacity-50">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                    <i class="fa-solid fa-users-slash text-3xl"></i>
                </div>
                <p class="text-sm font-bold tracking-wide text-slate-400">ยังไม่มีรายชื่อเจ้าหน้าที่ในระบบ</p>
            </div>
        @endforelse
    </div>

    @if($staffs->hasPages())
        <div class="rounded-[2.5rem] border border-slate-100 bg-white px-8 py-6 shadow-sm">
            {{ $staffs->links() }}
        </div>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-6 backdrop-blur-md animate-in fade-in duration-300">
            <div class="w-full max-w-xl overflow-hidden rounded-[2.5rem] bg-white shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="flex items-center justify-between border-b border-slate-50 px-10 pb-6 pt-10">
                    <div>
                        <h3 class="text-xl font-black tracking-tight text-slate-800">{{ $editingId ? 'แก้ไขข้อมูลเจ้าหน้าที่' : 'เพิ่มเจ้าหน้าที่ใหม่' }}</h3>
                        <p class="mt-0.5 text-[10px] font-bold uppercase tracking-widest text-slate-400">Credentials & Access Control</p>
                    </div>

                    <button wire:click="$set('showModal', false)" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-rose-50 hover:text-rose-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-6 p-10">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">ชื่อ-นามสกุล *</label>
                            <input wire:model="full_name" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                            @error('full_name') <span class="ml-1 mt-2 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Username *</label>
                            <input wire:model="username" type="text" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                            @error('username') <span class="ml-1 mt-2 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Email *</label>
                            <input wire:model="email" type="email" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                            @error('email') <span class="ml-1 mt-2 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">รหัสผ่าน {{ $editingId ? '(เว้นว่างถ้ายังไม่ต้องการเปลี่ยน)' : '*' }}</label>
                            <input wire:model="password" type="password" class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                            @error('password') <span class="ml-1 mt-2 block text-[10px] font-bold text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">บทบาท</label>
                            <select wire:model="role" class="w-full appearance-none rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                                <option value="staff">Staff (เจ้าหน้าที่ทั่วไป)</option>
                                <option value="nurse">Nurse (พยาบาล)</option>
                                <option value="doctor">Doctor (แพทย์)</option>
                                <option value="admin">Clinic Admin (ผู้ดูแลคลินิก)</option>
                            </select>
                        </div>

                        <div>
                            <label class="ml-1 mb-2.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">สถานะ</label>
                            <select wire:model="status" class="w-full appearance-none rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 text-sm font-bold text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-50">
                                <option value="active">Active (ใช้งานปกติ)</option>
                                <option value="disabled">Disabled (ปิดการใช้งาน)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-6">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 rounded-2xl bg-slate-100 py-5 font-black text-slate-500 transition-all active:scale-95">ยกเลิก</button>
                        <button type="submit" class="flex-[2] rounded-2xl bg-slate-900 py-5 font-black text-white shadow-xl shadow-slate-200 transition-all active:scale-95">
                            {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างบัญชีเจ้าหน้าที่' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
