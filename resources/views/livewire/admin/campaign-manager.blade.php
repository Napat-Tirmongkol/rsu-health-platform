<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">จัดการแคมเปญ</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">Campaign & Event Management</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative group">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-[#2e9e63] transition-colors"></i>
                <input wire:model.live="search" type="text" placeholder="ค้นหาชื่อแคมเปญ..." class="pl-11 pr-6 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] transition-all w-full md:w-72">
            </div>
            <button wire:click="openAddModal" class="bg-[#2e9e63] text-white px-8 py-3.5 rounded-2xl font-black shadow-lg shadow-green-100 hover:shadow-green-200 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3">
                <i class="fa-solid fa-plus-circle text-lg"></i>
                <span>สร้างแคมเปญ</span>
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

    <!-- Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">แคมเปญ / ประเภท</th>
                        <th class="px-6 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] text-center">โควต้า</th>
                        <th class="px-6 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] text-center">สถานะ</th>
                        <th class="px-6 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] text-center">วันสิ้นสุด</th>
                        <th class="px-8 py-6 text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($campaigns as $camp)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-[#2e9e63] group-hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid {{ $camp->type === 'vaccine' ? 'fa-syringe' : ($camp->type === 'training' ? 'fa-chalkboard-user' : 'fa-stethoscope') }} text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 mb-1">{{ $camp->title }}</h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $camp->type === 'vaccine' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                                            {{ $camp->type }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-700">{{ $camp->bookings_count ?? $camp->bookings()->count() }} / {{ $camp->total_capacity }}</span>
                                    <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                                        @php
                                            $percent = $camp->total_capacity > 0 ? min(100, ($camp->bookings()->count() / $camp->total_capacity) * 100) : 0;
                                        @endphp
                                        <div class="h-full bg-[#2e9e63] rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $camp->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $camp->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    {{ $camp->status === 'active' ? 'เปิดรับสมัคร' : 'ปิดชั่วคราว' }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="text-[11px] font-black text-slate-500 uppercase">
                                    {{ $camp->ends_at ? $camp->ends_at->format('d M Y') : '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="generateNewToken({{ $camp->id }})" title="รีเซ็ตลิงก์แชร์" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                        <i class="fa-solid fa-link text-xs"></i>
                                    </button>
                                    <button wire:click="edit({{ $camp->id }})" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button wire:click="delete({{ $camp->id }})" wire:confirm="คุณต้องการลบแคมเปญนี้ใช่หรือไม่?" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center opacity-40">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                                    <i class="fa-solid fa-box-open text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-400 tracking-wide">ยังไม่มีแคมเปญในระบบ</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($campaigns->hasPages())
            <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/30">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Layout -->
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="px-10 pt-10 pb-6 flex items-center justify-between border-b border-slate-50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $editingId ? 'แก้ไขแคมเปญ' : 'สร้างแคมเปญใหม่' }}</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Campaign Details & Configuration</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-10 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">ชื่อแคมเปญ/กิจกรรม</label>
                            <input wire:model="title" type="text" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all">
                            @error('title') <span class="text-[10px] text-rose-500 font-bold mt-2 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">ประเภท</label>
                            <select wire:model="type" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all appearance-none">
                                <option value="vaccine">ฉีดวัคซีน</option>
                                <option value="training">อบรม/สัมมนา</option>
                                <option value="health_check">ตรวจสุขภาพ</option>
                                <option value="other">กิจกรรมอื่นๆ</option>
                            </select>
                        </div>

                        <!-- Capacity -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">โควต้า (คน)</label>
                            <input wire:model="total_capacity" type="number" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all">
                        </div>

                        <!-- Dates -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">เริ่มเปิดจอง</label>
                            <input wire:model="starts_at" type="datetime-local" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">สิ้นสุดการจอง</label>
                            <input wire:model="ends_at" type="datetime-local" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all">
                        </div>

                        <!-- Status & Toggle -->
                        <div class="md:col-span-2 flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100">
                            <div>
                                <h4 class="text-sm font-black text-slate-800 leading-none mb-1">อนุมัติการจองอัตโนมัติ</h4>
                                <p class="text-[10px] text-slate-400 font-bold tracking-tight">ไม่ต้องให้แอดมินกดยืนยันรายการจอง</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_auto_approve" type="checkbox" class="sr-only peer">
                                <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#2e9e63]"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">คำอธิบายแคมเปญ</label>
                        <textarea wire:model="description" rows="4" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] focus:bg-white transition-all" placeholder="รายละเอียดแคมเปญ สิทธิการจอง และข้อควรปฏิบัติ..."></textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 py-4 bg-slate-100 text-slate-500 font-black rounded-2xl active:scale-95 transition-all">ยกเลิก</button>
                        <button type="submit" class="flex-[2] py-4 bg-slate-900 text-white font-black rounded-2xl shadow-xl shadow-slate-200 active:scale-95 transition-all">
                            {{ $editingId ? 'บันทึกการแก้ไข' : 'สร้างแคมเปญทันที' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
