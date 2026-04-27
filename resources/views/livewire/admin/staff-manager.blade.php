<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">จัดการเจ้าหน้าที่</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">Staff & Team Management</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative group">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-blue-500 transition-colors"></i>
                <input wire:model.live="search" type="text" placeholder="ค้นหาชื่อ, username..." class="pl-11 pr-6 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all w-full md:w-72">
            </div>
            <button wire:click="openAddModal" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-black shadow-lg shadow-slate-200 hover:shadow-slate-300 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <span>เพิ่มเจ้าหน้าที่</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-600 font-bold text-sm flex items-center gap-3 animate-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-check text-lg"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl text-rose-600 font-bold text-sm flex items-center gap-3 animate-in slide-in-from-top-4 duration-300">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Staff Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($staffs as $s)
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                @if($s->status === 'disabled')
                    <div class="absolute inset-0 bg-slate-50/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
                        <span class="px-4 py-1.5 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg">Disabled Account</span>
                    </div>
                @endif

                <div class="flex items-start justify-between mb-6">
                    <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 text-2xl border border-slate-100 shadow-inner group-hover:scale-110 transition-transform">
                        @php
                            $initials = mb_strtoupper(mb_substr($s->full_name ?: $s->username, 0, 1));
                        @endphp
                        <span class="font-black text-slate-400 group-hover:text-blue-500 transition-colors">{{ $initials }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $s->id }})" class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button wire:click="toggleStatus({{ $s->id }})" class="w-10 h-10 rounded-xl {{ $s->status === 'active' ? 'bg-emerald-50 text-emerald-500 hover:bg-emerald-500' : 'bg-rose-50 text-rose-500 hover:bg-rose-500' }} hover:text-white transition-all flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <h4 class="text-xl font-black text-slate-800 tracking-tight">{{ $s->full_name }}</h4>
                    <p class="text-xs text-slate-400 font-bold tracking-widest uppercase">@ {{ $s->username }}</p>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">บทบาท</span>
                        <span class="text-xs font-black text-slate-600 uppercase tracking-widest">{{ $s->role }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1 block">อีเมลติดต่อ</span>
                        <span class="text-xs font-bold text-slate-500">{{ $s->email }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-32 text-center opacity-40 bg-white rounded-[3rem] border border-slate-100 border-dashed">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <i class="fa-solid fa-users-slash text-3xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 tracking-wide uppercase tracking-[0.2em]">ยังไม่มีรายชื่อเจ้าหน้าที่ในระบบ</p>
            </div>
        @endforelse
    </div>

    @if($staffs->hasPages())
        <div class="bg-white px-8 py-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
            {{ $staffs->links() }}
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="px-10 pt-10 pb-6 flex items-center justify-between border-b border-slate-50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $editingId ? 'แก้ไขข้อมูลเจ้าหน้าที่' : 'เพิ่มเจ้าหน้าที่ใหม่' }}</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Credentials & Access Control</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">ชื่อ-นามสกุล *</label>
                            <input wire:model="full_name" type="text" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all">
                            @error('full_name') <span class="text-[10px] text-rose-500 font-bold mt-2 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Username *</label>
                            <input wire:model="username" type="text" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all">
                            @error('username') <span class="text-[10px] text-rose-500 font-bold mt-2 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Email *</label>
                            <input wire:model="email" type="email" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all">
                            @error('email') <span class="text-[10px] text-rose-500 font-bold mt-2 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">รหัสผ่าน {{ $editingId ? '(เว้นว่างถ้าไม่ต้องการเปลี่ยน)' : '*' }}</label>
                            <input wire:model="password" type="password" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all">
                            @error('password') <span class="text-[10px] text-rose-500 font-bold mt-2 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">บทบาท</label>
                            <select wire:model="role" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all appearance-none">
                                <option value="staff">Staff (เจ้าหน้าที่ทั่วไป)</option>
                                <option value="nurse">Nurse (พยาบาล)</option>
                                <option value="doctor">Doctor (แพทย์)</option>
                                <option value="admin">Clinic Admin (แอดมินคลินิก)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">สถานะ</label>
                            <select wire:model="status" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-50 focus:border-blue-500 focus:bg-white transition-all appearance-none">
                                <option value="active">Active (ใช้งานปกติ)</option>
                                <option value="disabled">Disabled (ปิดการใช้งาน)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-6">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 py-5 bg-slate-100 text-slate-500 font-black rounded-2xl active:scale-95 transition-all">ยกเลิก</button>
                        <button type="submit" class="flex-[2] py-5 bg-slate-900 text-white font-black rounded-2xl shadow-xl shadow-slate-200 active:scale-95 transition-all">
                            {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างบัญชีเจ้าหน้าที่' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
