<div>
    <button wire:click="toggle" class="relative flex h-10 w-10 items-center justify-center text-slate-400 transition-all hover:text-slate-600 active:scale-90">
        <i class="fa-solid fa-bell text-lg"></i>
        @if($unreadCount > 0)
            <span class="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-red-500 text-[9px] font-black text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    @if($isOpen)
        <div class="fixed inset-0 z-[9999] flex items-end justify-center bg-slate-900/60" wire:click.self="close">
            <div class="flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-t-[2.5rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-50 px-8 pb-4 pt-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black leading-tight text-slate-900">การแจ้งเตือน</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $unreadCount }} รายการยังไม่ได้อ่าน</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($unreadCount > 0)
                            <button wire:click="markAllAsRead" class="rounded-full bg-slate-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                อ่านทั้งหมด
                            </button>
                        @endif
                        <button wire:click="close" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="custom-scrollbar flex-1 space-y-4 overflow-y-auto p-6">
                    @forelse($announcements as $notif)
                        <div class="group rounded-3xl border p-5 transition-all {{ $notif->reads->isEmpty() ? 'border-emerald-100 bg-emerald-50/60' : 'border-slate-100 bg-slate-50 hover:bg-white hover:shadow-md' }}">
                            <div class="flex gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-100 bg-white text-green-600 transition-transform group-hover:scale-110">
                                    <i class="fa-solid fa-bullhorn text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center justify-between gap-3">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $notif->created_at->diffForHumans() }}</p>
                                        @if($notif->reads->isEmpty())
                                            <span class="rounded-full bg-emerald-500 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-white">ใหม่</span>
                                        @endif
                                    </div>
                                    <h4 class="mb-1.5 text-sm font-black leading-tight text-slate-800">{{ $notif->title }}</h4>
                                    <p class="line-clamp-2 text-[11px] leading-relaxed text-slate-500">{{ $notif->content }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center opacity-40">
                            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                                <i class="fa-solid fa-bell-slash text-3xl"></i>
                            </div>
                            <p class="text-sm font-bold tracking-wide text-slate-400">ยังไม่มีการแจ้งเตือนในขณะนี้</p>
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-slate-50 bg-slate-50/50 p-6">
                    <button wire:click="close" class="h-16 w-full rounded-2xl bg-slate-900 font-black text-white shadow-xl transition-all active:scale-95">
                        ปิดการแจ้งเตือน
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
