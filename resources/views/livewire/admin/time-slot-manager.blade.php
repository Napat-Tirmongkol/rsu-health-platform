<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header & Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">จัดการรอบเวลา</h1>
            <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mt-1">Time Slots & Capacity Planning</p>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <!-- Campaign Filter -->
            <div class="relative group">
                <select wire:model.live="filterCampId" class="pl-6 pr-12 py-3.5 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] transition-all appearance-none min-w-[200px]">
                    <option value="all">แสดงทุกแคมเปญ</option>
                    @foreach($campaigns as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <!-- Month Navigator -->
            <div class="flex items-center bg-white border border-slate-100 rounded-2xl shadow-sm p-1">
                <button wire:click="prevMonth" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-[#2e9e63] hover:bg-green-50 rounded-xl transition-all">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <div class="px-6 text-sm font-black text-slate-700 uppercase tracking-widest min-w-[140px] text-center">
                    {{ $monthName }}
                </div>
                <button wire:click="nextMonth" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-[#2e9e63] hover:bg-green-50 rounded-xl transition-all">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>

            <button wire:click="openAddModal()" class="bg-[#2e9e63] text-white px-8 py-3.5 rounded-2xl font-black shadow-lg shadow-green-100 hover:shadow-green-200 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3">
                <i class="fa-solid fa-calendar-plus text-lg"></i>
                <span>สร้างรอบเวลา</span>
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

    <!-- Calendar Grid -->
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden overflow-x-auto custom-scrollbar">
        <div class="min-w-[1000px]">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 bg-slate-50/50 border-b border-slate-100">
                @foreach(['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'] as $day)
                    <div class="py-4 text-center text-[10px] font-black uppercase tracking-[0.2em] {{ $loop->first ? 'text-rose-400' : 'text-slate-400' }}">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            <!-- Day Cells -->
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $dayData)
                    @php
                        $isToday = $dayData['date']->isToday();
                        $isCurrentMonth = $dayData['isCurrentMonth'];
                    @endphp
                    <div class="min-h-[160px] p-4 border-r border-b border-slate-50 relative group transition-colors {{ !$isCurrentMonth ? 'bg-slate-50/30 opacity-40' : 'hover:bg-slate-50/50' }}">
                        <div class="flex justify-between items-center mb-4">
                            <span class="w-8 h-8 flex items-center justify-center text-sm font-black rounded-full {{ $isToday ? 'bg-[#2e9e63] text-white shadow-lg shadow-green-200' : 'text-slate-700' }}">
                                {{ $dayData['date']->day }}
                            </span>
                            @if($isCurrentMonth)
                                <button wire:click="openAddModal('{{ $dayData['date']->format('Y-m-d') }}')" class="w-7 h-7 bg-white border border-slate-100 rounded-lg text-slate-300 hover:text-[#2e9e63] hover:border-[#2e9e63] hover:shadow-sm transition-all opacity-0 group-hover:opacity-100 flex items-center justify-center">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                </button>
                            @endif
                        </div>

                        <!-- Slots List -->
                        <div class="space-y-2 max-h-[140px] overflow-y-auto no-scrollbar">
                            @foreach($dayData['slots'] as $slot)
                                @php
                                    $percent = $slot->max_slots > 0 ? ($slot->bookings_count / $slot->max_slots) * 100 : 0;
                                    $statusColor = $percent >= 100 ? 'bg-rose-50 text-rose-600 border-rose-100' : ($percent >= 80 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100');
                                @endphp
                                <div class="p-2 rounded-xl border {{ $statusColor }} text-[10px] font-black group/slot relative overflow-hidden transition-all hover:scale-105 hover:z-10 hover:shadow-md">
                                    <div class="flex justify-between items-center relative z-10">
                                        <span>{{ substr($slot->start_time, 0, 5) }}</span>
                                        <span class="opacity-60">{{ $slot->bookings_count }}/{{ $slot->max_slots }}</span>
                                    </div>
                                    <div class="w-full h-1 bg-black/5 rounded-full mt-1.5 relative z-10 overflow-hidden">
                                        <div class="h-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-[#2e9e63]') }}" style="width: {{ min(100, $percent) }}%"></div>
                                    </div>
                                    
                                    <!-- Quick Actions on Hover -->
                                    <div class="absolute inset-0 bg-white/95 flex items-center justify-center gap-2 opacity-0 group-hover/slot:opacity-100 transition-opacity z-20">
                                        <button wire:click="deleteSlot({{ $slot->id }})" wire:confirm="ต้องการลบรอบเวลานี้ใช่หรือไม่?" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Batch Add Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="px-10 pt-10 pb-6 flex items-center justify-between border-b border-slate-50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">กำหนดรอบเวลารับคิว</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Batch Slot Generation & Capacity Allocation</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-10 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Campaign Select -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">เลือกแคมเปญกิจกรรม</label>
                            <select wire:model="camp_id" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-green-50 focus:border-[#2e9e63] transition-all appearance-none">
                                <option value="">-- กรุณาเลือกแคมเปญ --</option>
                                @foreach($campaigns as $camp)
                                    <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Multi Date Select (Simplified for now) -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">วันที่ต้องการจัดกิจกรรม (YYYY-MM-DD)</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selected_dates as $index => $date)
                                    <div class="flex items-center gap-2 px-4 py-2 bg-[#2e9e63] text-white text-xs font-black rounded-xl">
                                        {{ $date }}
                                        <button type="button" wire:click="unset('selected_dates.{{ $index }}')" class="hover:text-rose-200"><i class="fa-solid fa-times"></i></button>
                                    </div>
                                @endforeach
                                <input type="date" wire:keydown.enter.prevent="$set('selected_dates', [...selected_dates, $event.target.value])" onchange="@this.set('selected_dates', [...@js($selected_dates), this.value])" class="px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#2e9e63]">
                            </div>
                        </div>

                        <!-- Time Rows -->
                        <div class="md:col-span-2 space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">ช่วงเวลารับคิวต่อวัน</label>
                            @foreach($start_times as $index => $startTime)
                                <div class="flex items-end gap-4 animate-in slide-in-from-left-4 duration-300">
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-black text-slate-300 uppercase mb-2">เริ่ม</label>
                                        <input wire:model="start_times.{{ $index }}" type="time" class="w-full px-6 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2e9e63] transition-all">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-black text-slate-300 uppercase mb-2">สิ้นสุด</label>
                                        <input wire:model="end_times.{{ $index }}" type="time" class="w-full px-6 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#2e9e63] transition-all">
                                    </div>
                                    @if($loop->count > 1)
                                        <button type="button" wire:click="removeTimeRow({{ $index }})" class="w-12 h-12 bg-rose-50 text-rose-400 rounded-2xl hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                            <button type="button" wire:click="addTimeRow" class="w-full py-3 border border-dashed border-slate-200 text-slate-400 font-bold rounded-2xl hover:border-[#2e9e63] hover:text-[#2e9e63] hover:bg-green-50 transition-all text-xs">
                                <i class="fa-solid fa-plus-circle mr-2"></i> เพิ่มช่วงเวลาอื่นในวันเดียวกัน
                            </button>
                        </div>

                        <!-- Capacity Split -->
                        <div class="md:col-span-2 p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">จำนวนที่นั่งรวม (โควต้าต่อวัน)</label>
                            <div class="flex items-center gap-6">
                                <input wire:model="max_slots" type="number" class="w-32 px-6 py-4 bg-white border border-slate-100 rounded-2xl text-xl font-black text-[#2e9e63] focus:outline-none focus:ring-4 focus:ring-green-100 focus:border-[#2e9e63] transition-all text-center shadow-inner">
                                <div class="text-xs font-bold text-slate-400 leading-relaxed">
                                    ระบบจะทำการหารเฉลี่ยให้โดยอัตโนมัติ<br>
                                    <span class="text-[#2e9e63]">เหลือเศษจะทบไปที่รอบแรกของวัน</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-6">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 py-5 bg-slate-100 text-slate-500 font-black rounded-2xl active:scale-95 transition-all">ยกเลิก</button>
                        <button type="submit" class="flex-[2] py-5 bg-slate-900 text-white font-black rounded-2xl shadow-xl shadow-slate-200 active:scale-95 transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-magic-wand-sparkles"></i>
                            <span>คำนวณและสร้างรอบเวลาทันที</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
