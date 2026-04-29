<div class="space-y-6">
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="space-y-6 rounded-[2.5rem] border border-slate-50 bg-white p-8 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700">คำนำหน้าชื่อ <span class="text-red-500">*</span></label>
                <select wire:model.live="prefix" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none transition-all focus:ring-4 focus:ring-green-50">
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
                        <input type="text" wire:model="custom_prefix" placeholder="ระบุคำนำหน้าด้วยตัวเอง..." class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-5 font-bold text-slate-700 outline-none">
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">ชื่อจริง <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="first_name" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                    @error('first_name') <p class="ml-2 text-[10px] font-bold text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">นามสกุล <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="last_name" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                    @error('last_name') <p class="ml-2 text-[10px] font-bold text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">เพศ <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    @foreach(['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'] as $val => $lbl)
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model="gender" value="{{ $val }}" class="peer hidden">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 py-4 text-center text-sm font-bold text-slate-400 transition-all peer-checked:border-[#2e9e63] peer-checked:bg-[#2e9e63] peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-green-100">
                                {{ $lbl }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="my-2 h-px bg-slate-50"></div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">ประเภทผู้ใช้ <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['student' => 'นักศึกษา', 'staff' => 'บุคลากร', 'other' => 'บุคคลทั่วไป'] as $val => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="status" value="{{ $val }}" class="peer hidden">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 py-4 text-center text-[11px] font-bold text-slate-400 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-500 peer-checked:text-white">
                                {{ $lbl }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-5 text-left">
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-slate-700">เลขบัตรประชาชน / Passport <span class="text-red-500">*</span></label>
                    <div class="mb-3 flex gap-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="id_type" value="citizen" class="peer hidden">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 py-2.5 text-center text-[10px] font-bold transition-all peer-checked:border-green-200 peer-checked:bg-green-50 peer-checked:text-[#2e9e63]">บัตรประชาชนไทย</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="id_type" value="passport" class="peer hidden">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 py-2.5 text-center text-[10px] font-bold transition-all peer-checked:border-green-200 peer-checked:bg-green-50 peer-checked:text-[#2e9e63]">Passport</div>
                        </label>
                    </div>
                    <input type="text" wire:model="citizen_id" placeholder="{{ $id_type === 'citizen' ? 'เลขบัตรประชาชน 13 หลัก' : 'เลขพาสปอร์ต' }}" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                </div>

                @if($status !== 'other')
                    <div class="space-y-1.5 animate-in slide-in-from-top-2 duration-300">
                        <label class="text-sm font-bold text-slate-700">รหัสนักศึกษา / รหัสบุคลากร <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="student_id" placeholder="ระบุรหัสประจำตัวของคุณ" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                    </div>
                @endif
            </div>

            <div class="space-y-1.5 text-left">
                <label class="text-sm font-bold text-slate-700">คณะ / หน่วยงาน <span class="text-red-500">*</span></label>
                <input type="text" wire:model="department" list="faculties-list" placeholder="ระบุคณะหรือหน่วยงานที่สังกัด" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                <datalist id="faculties-list">
                    @foreach($faculties as $fac)
                        <option value="{{ $fac }}">
                    @endforeach
                </datalist>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                    <input type="tel" wire:model="phone_number" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                </div>
                <div class="space-y-1.5 text-left">
                    <label class="text-sm font-bold text-slate-700">อีเมล (ถ้ามี)</label>
                    <input type="email" wire:model="email" placeholder="example@rsu.ac.th" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 font-bold text-slate-700 outline-none focus:ring-4 focus:ring-green-50">
                    <p class="mt-2 px-1 text-[10px] font-bold text-amber-600">
                        <i class="fa-solid fa-circle-info mr-1"></i> ใช้อีเมลมหาวิทยาลัยเพื่อรับข่าวสารและสิทธิพิเศษจากคลินิกได้สะดวกขึ้น
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6 rounded-[2.5rem] border border-slate-50 bg-white p-8 shadow-sm">
            <h3 class="text-left text-sm font-black uppercase tracking-widest text-slate-900">Privacy &amp; PDPA</h3>
            <div class="custom-scrollbar max-h-48 space-y-4 overflow-y-auto rounded-3xl border border-slate-100 bg-slate-50 p-6 text-left text-[11px] leading-relaxed text-slate-500">
                <div class="mb-1 font-black text-slate-900">ยินดีต้อนรับสู่ระบบ RSU Medical Hub</div>
                <p>เราใช้ข้อมูลของคุณเพื่อยืนยันตัวตน ให้บริการทางการแพทย์ ประสานงานสิทธิการรักษา และแจ้งเตือนนัดหมายหรือกิจกรรมสำคัญของคลินิก</p>
                <p>คุณสามารถขอแก้ไขหรือลบข้อมูลได้ตามนโยบาย PDPA ของมหาวิทยาลัยรังสิต</p>
            </div>
            <label class="flex cursor-pointer items-center gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-5 text-left transition-all active:scale-95">
                <input type="checkbox" wire:model="agreed" class="h-6 w-6 rounded-lg border-slate-200 text-[#2e9e63] focus:ring-[#2e9e63]">
                <span class="text-[11px] font-bold leading-tight text-slate-600">ฉันอ่านและเข้าใจนโยบายความเป็นส่วนตัวแล้ว และยืนยันว่าข้อมูลทั้งหมดเป็นความจริง</span>
            </label>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('user.hub') }}" class="flex h-16 flex-1 items-center justify-center rounded-2xl border border-slate-200 bg-white font-black text-slate-400">ยกเลิก</a>
            <button type="submit" wire:loading.attr="disabled" class="flex h-16 flex-[2] items-center justify-center gap-2 rounded-2xl bg-slate-900 font-black text-white shadow-xl shadow-slate-200 transition-all active:scale-95">
                <span wire:loading.remove>บันทึกข้อมูลส่วนตัว</span>
                <span wire:loading class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-notch animate-spin"></i> กำลังบันทึก...
                </span>
            </button>
        </div>
    </form>
</div>
