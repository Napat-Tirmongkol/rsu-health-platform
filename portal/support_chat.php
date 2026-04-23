<?php
// portal/support_chat.php — Premium Staff Support Chat Center
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = db();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat - Central HUB</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Prompt:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', 'Prompt', sans-serif; background: #F8FAFF; }
        .chat-container { height: calc(100vh - 120px); }
        .user-list { width: 350px; border-right: 1px solid #E2E8F0; }
        .chat-window { flex: 1; display: flex; flex-direction: column; }
        .message-bubble { max-width: 70%; padding: 12px 18px; border-radius: 20px; font-size: 14px; line-height: 1.5; }
        .message-user { background: #FFFFFF; border: 1px solid #E2E8F0; align-self: flex-start; border-bottom-left-radius: 4px; }
        .message-staff { background: #2563EB; color: #FFFFFF; align-self: flex-end; border-bottom-right-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        .active-user { background: #EFF6FF; border-left: 4px solid #2563EB; }
    </style>
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Live Support Chat</h1>
                <p class="text-slate-500 font-medium">จัดการข้อความตอบกลับผู้ใช้งานแบบ Real-time</p>
            </div>
            <a href="index.php" class="bg-white border border-slate-200 px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/5 overflow-hidden flex chat-container border border-slate-100">
            <!-- Sidebar: User List -->
            <div class="user-list flex flex-col bg-white">
                <div class="p-6 border-b border-slate-50">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Search conversation..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-medium focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>
                </div>
                <div id="user-list-container" class="flex-1 overflow-y-auto custom-scrollbar">
                    <!-- Loading State -->
                    <div class="p-10 text-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Main Chat Window -->
            <div id="chat-window-placeholder" class="chat-window items-center justify-center bg-slate-50/50">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-4 shadow-xl border border-slate-100">
                        <i class="fa-solid fa-comments text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg">เลือกการสนทนา</h3>
                    <p class="text-slate-400 text-sm">คลิกที่รายชื่อด้านซ้ายเพื่อเริ่มตอบกลับ</p>
                </div>
            </div>

            <div id="chat-window-active" class="chat-window hidden">
                <!-- Chat Header -->
                <div class="px-8 py-5 border-b border-slate-50 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <img id="active-user-img" src="" class="w-11 h-11 rounded-2xl object-cover border-2 border-white shadow-sm">
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-[3px] border-white"></span>
                        </div>
                        <div>
                            <h3 id="active-user-name" class="text-slate-900 font-bold text-base leading-none mb-1">...</h3>
                            <p class="text-emerald-500 text-[10px] font-black uppercase tracking-widest">Online Now</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-8 space-y-6 bg-[#F8FAFF] flex flex-col custom-scrollbar">
                    <!-- Messages will be injected here -->
                </div>

                <!-- Input Area -->
                <div class="p-8 bg-white border-t border-slate-50">
                    <form id="chat-form" onsubmit="handleStaffSubmit(event)" class="relative">
                        <input type="text" id="chat-input" placeholder="พิมพ์ข้อความตอบกลับ..." class="w-full h-16 bg-slate-50 border-none rounded-2xl pl-6 pr-24 text-sm font-bold focus:ring-4 focus:ring-blue-100 transition-all">
                        <button type="submit" class="absolute right-2 top-2 h-12 px-6 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 active:scale-95 transition-all flex items-center gap-2">
                            <span>ส่ง</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let lastMessageId = 0;

        async function loadUsers() {
            const res = await fetch('ajax_support_chat.php?action=list_users');
            const data = await res.json();
            if (data.success) {
                const container = document.getElementById('user-list-container');
                container.innerHTML = data.users.map(u => `
                    <div onclick="selectUser(${u.id}, '${u.full_name}', '${u.picture_url}')" class="p-6 border-b border-slate-50 cursor-pointer hover:bg-slate-50 transition-all flex items-start gap-4 ${currentUserId == u.id ? 'active-user' : ''}">
                        <img src="${u.picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.full_name)}" class="w-12 h-12 rounded-2xl object-cover shadow-sm">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="text-slate-900 font-bold text-sm truncate">${u.full_name}</h4>
                                <span class="text-[9px] text-slate-400 font-bold uppercase">${new Date(u.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                            <p class="text-slate-500 text-xs truncate font-medium">${u.last_message}</p>
                        </div>
                        ${u.unread_count > 0 ? `<span class="bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">${u.unread_count}</span>` : ''}
                    </div>
                `).join('');
            }
        }

        async function selectUser(id, name, img) {
            currentUserId = id;
            document.getElementById('chat-window-placeholder').classList.add('hidden');
            document.getElementById('chat-window-active').classList.remove('hidden');
            document.getElementById('active-user-name').innerText = name;
            document.getElementById('active-user-img').src = img || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name);
            loadUsers(); // Refresh list to update active state
            loadMessages();
        }

        async function loadMessages() {
            if (!currentUserId) return;
            const res = await fetch(`ajax_support_chat.php?action=get_messages&user_id=${currentUserId}`);
            const data = await res.json();
            if (data.success) {
                const container = document.getElementById('messages-container');
                container.innerHTML = data.messages.map(m => `
                    <div class="message-bubble ${m.sender_type === 'staff' ? 'message-staff' : 'message-user'}">
                        <p class="font-medium">${m.message}</p>
                        <div class="mt-1 flex items-center justify-between gap-4 opacity-50">
                            <span class="text-[9px] font-black uppercase">${m.sender_type === 'staff' ? 'คุณ' : 'ผู้ใช้งาน'}</span>
                            <span class="text-[9px] font-black">${m.time}</span>
                        </div>
                    </div>
                `).join('');
                container.scrollTop = container.scrollHeight;
            }
        }

        async function handleStaffSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (!message || !currentUserId) return;

            const formData = new FormData();
            formData.append('user_id', currentUserId);
            formData.append('message', message);

            const res = await fetch('ajax_support_chat.php?action=send_reply', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                input.value = '';
                loadMessages();
            }
        }

        // Initial Load
        loadUsers();
        setInterval(loadUsers, 5000);
        setInterval(loadMessages, 3000);
    </script>
</body>
</html>
