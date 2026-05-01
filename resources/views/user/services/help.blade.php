<x-user-layout>
    <x-slot name="title">Help - RSU Medical</x-slot>

    <main class="space-y-8 px-6 py-8">
        <div class="space-y-2">
            <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
                <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">Help</h2>
            <p class="text-xs font-medium text-slate-500">คำตอบสั้นๆ สำหรับคำถามที่ผู้ใช้มักเจอบ่อยในระบบจองบริการ</p>
        </div>

        <section class="space-y-4">
            <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-black text-slate-900">จองแล้วต้องทำอะไรต่อ</p>
                <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">หลังจองสำเร็จ ให้ตรวจสอบรายการนัดหมายในหน้า History และแสดง QR หรือข้อมูลการจองต่อเจ้าหน้าที่เมื่อเข้ารับบริการ</p>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-black text-slate-900">ถ้าไม่เจอช่วงเวลาให้เลือก</p>
                <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">ลองเปลี่ยนวันที่หรือกลับมาเช็กภายหลัง หากยังไม่พบ สามารถติดต่อเจ้าหน้าที่ผ่านแชทเพื่อสอบถามรอบที่เปิดรับ</p>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-black text-slate-900">ถ้าต้องการยกเลิกนัดหมาย</p>
                <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">เข้าไปที่หน้า History แล้วเลือกการจองที่ยังรอรับบริการ จากนั้นกดยกเลิกนัดหมายได้จากรายการหรือดูรายละเอียดก่อนก็ได้</p>
            </div>
        </section>

        <section class="space-y-3 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900">ยังต้องการความช่วยเหลือ</h3>
            <a href="{{ route('user.chat') }}" class="flex items-center justify-between rounded-2xl bg-amber-50 px-4 py-4 font-black text-amber-700">
                <span><i class="fa-solid fa-life-ring mr-2"></i>ติดต่อเจ้าหน้าที่ผ่านแชท</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
            <a href="{{ route('user.services.contact') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 font-black text-slate-700">
                <span><i class="fa-solid fa-phone mr-2"></i>ดูช่องทางติดต่อทั้งหมด</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
        </section>
    </main>
</x-user-layout>
