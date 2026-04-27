<div class="space-y-6">
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-sm space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700">คำนำหน้าชื่อ <span class="text-red-500">*</span></label>
                <select wire:model.live="prefix" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none transition-all font-bold text-slate-700">
                    <option value="">เลือกคำนำหน้า</option>
                    <option value="นาย">นาย</option>
                    <option value="นาง">นาง</option>
                    <option value="นางสาว">นางสาว</option>
                    <optgroup label="การแพทย์">
                        <option value="นพ.">นพ.</option>
                        <option value="พญ.">พญ.</option>
                        <option value="ทพ.">ทพ.</option>
                        <option value="ทญ.">ทญ.</option>
                        <option value="ภก.">ภก.</option>
                        <option value="ภญ.">ภญ.</option>
                        <option value="พย.">พย.</option>
                    </optgroup>
                    <optgroup label="วิชาการ">
                        <option value="ดร.">ดร.</option>
                        <option value="อ.">อ.</option>
                        <option value="ผศ.">ผศ.</option>
                        <option value="รศ.">รศ.</option>
                        <option value="ศ.">ศ.</option>
                    </optgroup>
                    <option value="other">อื่นๆ...</option>
                </select>

                @if($prefix === 'other')
                    <div class="mt-3 animate-in slide-in-from-top-2 duration-300">
                        <input type="text" wire:model="custom_prefix" placeholder="ระบุคำนำหน้าด้วยตัวเอง..." class="w-full h-12 px-5 bg-white border border-slate-200 rounded-2xl outline-none font-bold text-slate-700">
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">ชื่อจริง <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="first_name" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                    @error('first_name') <p class="text-[10px] text-red-500 font-bold ml-2">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">นามสกุล <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="last_name" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                    @error('last_name') <p class="text-[10px] text-red-500 font-bold ml-2">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">เพศ <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    @foreach(['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'] as $val => $lbl)
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model="gender" value="{{ $val }}" class="peer hidden">
                            <div class="py-4 text-center rounded-2xl border border-slate-100 bg-slate-50 font-bold text-sm text-slate-400 peer-checked:bg-[#2e9e63] peer-checked:text-white peer-checked:border-[#2e9e63] peer-checked:shadow-lg peer-checked:shadow-green-100 transition-all">
                                {{ $lbl }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="h-px bg-slate-50 my-2"></div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">ประเภทผู้ใช้ <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['student' => 'นักศึกษา', 'staff' => 'บุคลากร', 'other' => 'ทั่วไป'] as $val => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="status" value="{{ $val }}" class="peer hidden">
                            <div class="py-4 text-center rounded-2xl border border-slate-100 bg-slate-50 font-bold text-[11px] text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 transition-all">
                                {{ $lbl }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-5 text-left">
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-slate-700">เลขบัตรประชาชน / Passport <span class="text-red-500">*</span></label>
                    <div class="flex gap-2 mb-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="id_type" value="citizen" class="peer hidden">
                            <div class="py-2.5 text-center border border-slate-100 bg-slate-50 rounded-xl peer-checked:bg-green-50 peer-checked:border-green-200 peer-checked:text-[#2e9e63] font-bold text-[10px] transition-all">บัตรประชาชนไทย</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="id_type" value="passport" class="peer hidden">
                            <div class="py-2.5 text-center border border-slate-100 bg-slate-50 rounded-xl peer-checked:bg-green-50 peer-checked:border-green-200 peer-checked:text-[#2e9e63] font-bold text-[10px] transition-all">Passport</div>
                        </label>
                    </div>
                    <input type="text" wire:model="citizen_id" placeholder="{{ $id_type === 'citizen' ? 'เลขบัตรประชาชน 13 หลัก' : 'เลขพาสปอร์ต' }}" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                </div>

                @if($status !== 'other')
                    <div class="space-y-1.5 animate-in slide-in-from-top-2 duration-300">
                        <label class="text-sm font-bold text-slate-700">รหัสนักศึกษา / รหัสบุคลากร <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="student_id" placeholder="ระบุรหัสประจำตัวของคุณ" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                    </div>
                @endif
            </div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">คณะ / หน่วยงาน <span class="text-red-500">*</span></label>
                <input type="text" wire:model="department" list="faculties-list" placeholder="ระบุคณะหรือหน่วยงานที่สังกัด" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                <datalist id="faculties-list">
                    @foreach($faculties as $fac)
                        <option value="{{ $fac }}">
                    @endforeach
                </datalist>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                    <input type="tel" wire:model="phone_number" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                </div>
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">อีเมล (ถ้ามี)</label>
                    <input type="email" wire:model="email" placeholder="example@rsu.ac.th" class="w-full h-14 px-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-green-50 outline-none font-bold text-slate-700">
                    <p class="text-[10px] text-amber-600 font-bold mt-2 px-1">
                        <i class="fa-solid fa-circle-info mr-1"></i> ใช้อีเมลมหาวิทยาลัยเพื่อรับข่าวสารและสิทธิพิเศษ
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-sm space-y-6">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest text-left">Privacy & PDPA</h3>
            <div class="bg-slate-50 p-6 rounded-3xl text-[11px] text-slate-500 leading-relaxed max-h-48 overflow-y-auto border border-slate-100 space-y-4 text-left custom-scrollbar">
                <div class="text-slate-900 font-black mb-1">ยินดีต้อนรับสู่ระบบ RSU Medical Hub</div>
                <p>เราจะใช้ข้อมูลของคุณเพื่อยืนยันตัวตน ให้บริการทางการแพทย์ ประสานงานสิทธิการรักษา และแจ้งเตือนนัดหมายหรือกิจกรรมสำคัญของคลินิก</p>
                <p>คุณสามารถขอแก้ไขหรือลบข้อมูลได้ตามนโยบาย PDPA ของมหาวิทยาลัยรังสิต</p>
            </div>
            <label class="flex items-center gap-4 p-5 bg-slate-50 rounded-3xl border border-slate-100 cursor-pointer active:scale-95 transition-all text-left">
                <input type="checkbox" wire:model="agreed" class="w-6 h-6 rounded-lg text-[#2e9e63] border-slate-200 focus:ring-[#2e9e63]">
                <span class="text-[11px] text-slate-600 font-bold leading-tight">ฉันอ่านและเข้าใจนโยบายความเป็นส่วนตัวแล้ว และยืนยันว่าข้อมูลทั้งหมดเป็นความจริง</span>
            </label>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('user.hub') }}" class="flex-1 h-16 bg-white border border-slate-200 text-slate-400 font-black rounded-2xl flex items-center justify-center">ยกเลิก</a>
            <button type="submit" wire:loading.attr="disabled" class="flex-[2] h-16 bg-slate-900 text-white font-black rounded-2xl shadow-xl shadow-slate-200 active:scale-95 transition-all flex items-center justify-center gap-2">
                <span wire:loading.remove>บันทึกข้อมูลส่วนตัว</span>
                <span wire:loading class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-notch animate-spin"></i> กำลังบันทึก...
                </span>
            </button>
        </div>
    </form>
</div>
