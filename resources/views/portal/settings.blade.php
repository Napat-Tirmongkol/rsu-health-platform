<x-portal-layout title="Global Site Settings">
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-6 lg:px-8">
            <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm font-bold text-amber-700 shadow-sm">
                หน้านี้เป็นฐานของ portal settings รอบแรก โดยแสดง global keys ก่อน แล้วเราค่อยต่อยอดเป็นฟอร์มจัดการ branding และ per-clinic overrides ในรอบถัดไป
            </div>

            <section class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">
                                <th class="px-6 py-5">Key</th>
                                <th class="px-6 py-5">Type</th>
                                <th class="px-6 py-5">Value Preview</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($globalSettings as $setting)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-6 py-5 font-black text-slate-900">{{ $setting->key }}</td>
                                    <td class="px-6 py-5">
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-slate-600">
                                            {{ $setting->type ?: 'string' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-sm font-bold text-slate-500">{{ \Illuminate\Support\Str::limit((string) $setting->value, 80) ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-16 text-center text-sm font-bold text-slate-400">ยังไม่มี global settings ระดับระบบกลาง</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($globalSettings->hasPages())
                    <div class="border-t border-slate-100 px-6 py-5">
                        {{ $globalSettings->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-portal-layout>
