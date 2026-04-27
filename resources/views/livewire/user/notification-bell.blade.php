<div>
    <button wire:click="toggle" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors relative active:scale-90 transition-all">
        <i class="fa-solid fa-bell text-lg"></i>
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full border-2 border-white flex items-center justify-center">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    @if($isOpen)
        <div class="fixed inset-0 z-[9999] flex items-end justify-center bg-slate-900/60" wire:click.self="close">
            <div class="w-full max-w-md max-h-[85vh] overflow-hidden rounded-t-[2.5rem] bg-white shadow-2xl flex flex-col">
                <div class="px-8 pt-8 pb-4 flex items-center justify-between border-b border-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h3 class="text-slate-900 font-black text-lg leading-tight">การแจ้งเตือน</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $unreadCount }} รายการใหม่</p>
                        </div>
                    </div>
                    <button wire:click="close" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                    @forelse($announcements as $notif)
                        <div class="bg-slate-50 rounded-3xl p-5 border border-slate-100 hover:bg-white hover:shadow-md transition-all group">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-green-600 border border-slate-100 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-bullhorn text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    <h4 class="text-slate-800 font-black text-sm leading-tight mb-1.5">{{ $notif->title }}</h4>
                                    <p class="text-slate-500 text-[11px] leading-relaxed line-clamp-2">{{ $notif->content }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center opacity-40">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <i class="fa-solid fa-bell-slash text-3xl"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-400 tracking-wide">ยังไม่มีการแจ้งเตือนในขณะนี้</p>
                        </div>
                    @endforelse
                </div>

                <div class="p-6 border-t border-slate-50 bg-slate-50/50">
                    <button wire:click="close" class="w-full h-16 bg-slate-900 text-white font-black rounded-2xl shadow-xl active:scale-95 transition-all">
                        ปิดการแจ้งเตือน
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
