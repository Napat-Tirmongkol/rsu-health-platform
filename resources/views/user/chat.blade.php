<x-user-layout>
    <x-slot name="title">ติดต่อเจ้าหน้าที่ - RSU Health</x-slot>

    <div class="mb-6 space-y-2">
        <h2 class="text-2xl font-black tracking-tight text-slate-800">แชทติดต่อสอบถาม</h2>
        <p class="text-xs font-medium text-slate-500">สอบถามข้อมูลเพิ่มเติมกับเจ้าหน้าที่คลินิก</p>
    </div>

    <livewire:user.support-chat />

    <div class="mt-6 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4">
        <i class="fa-solid fa-circle-info mt-0.5 text-xs text-emerald-600"></i>
        <p class="text-[10px] font-medium leading-relaxed text-emerald-700">
            เจ้าหน้าที่จะตอบกลับในเวลาทำการ 08:30 - 16:30 น. หากเป็นกรณีฉุกเฉิน กรุณาติดต่อสายด่วนคลินิกโดยตรง
        </p>
    </div>
</x-user-layout>
