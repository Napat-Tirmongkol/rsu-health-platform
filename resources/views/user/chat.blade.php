<x-user-layout>
    <x-slot name="title">ติดต่อเจ้าหน้าที่ - RSU Health</x-slot>

    <div class="mb-6 space-y-2">
        <a href="{{ route('user.hub') }}" class="mb-2 flex items-center gap-1 text-xs font-bold text-slate-400">
            <i class="fa-solid fa-chevron-left"></i> กลับหน้าหลัก
        </a>
        <h2 class="text-2xl font-black tracking-tight text-slate-800">แชตติดต่อเจ้าหน้าที่</h2>
        <p class="text-xs font-medium text-slate-500">สอบถามข้อมูลเพิ่มเติม นัดหมาย หรือปัญหาการใช้งานกับทีมคลินิกได้ที่นี่</p>
    </div>

    <livewire:user.support-chat />

    <div class="mt-6 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4">
        <i class="fa-solid fa-circle-info mt-0.5 text-xs text-emerald-600"></i>
        <p class="text-[10px] font-medium leading-relaxed text-emerald-700">
            เจ้าหน้าที่จะตอบกลับในเวลาทำการ 08:30 - 16:30 น. หากเป็นกรณีเร่งด่วน กรุณาติดต่อคลินิกโดยตรงผ่านช่องทางโทรศัพท์
        </p>
    </div>
</x-user-layout>
