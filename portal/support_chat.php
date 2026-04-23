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
        @font-face {
            font-family: 'RSU';
            src: url('../assets/fonts/RSU_Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'RSU';
            src: url('../assets/fonts/RSU_BOLD.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'RSU', 'Outfit', 'Prompt', sans-serif;
            background-color: #F8FAFF;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            height: calc(100vh - 180px);
            background: #fff;
        }

        .user-list {
            width: 380px;
            border-right: 1px solid #F1F5F9;
        }

        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .message-bubble {
            max-width: 80%;
            padding: 14px 20px;
            border-radius: 24px;
            font-size: 15px;
            line-height: 1.6;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .message-user {
            background: #FFFFFF;
            border: 1px solid #F1F5F9;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            color: #334155;
        }

        .message-staff {
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            color: #FFFFFF;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E2E8F0;
            border-radius: 10px;
        }

        .active-user {
            background: #F0F7FF;
            border-right: 4px solid #2563EB;
        }

        .premium-input {
            border: 1.5px solid #F1F5F9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-input:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: #fff;
        }

        .status-pulse {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
    </style>
</head>

<body class="p-4 md:p-8 lg:p-12">

    <div class="max-w-[1600px] mx-auto h-full flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8 shrink-0">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-1">Live Support Chat</h1>
                <p class="text-slate-400 font-bold text-sm uppercase tracking-widest opacity-60">Real-time Patient Support Center</p>
            </div>
            <a href="index.php"
                class="bg-white border border-slate-200 px-6 py-3 rounded-2xl font-black text-slate-600 hover:text-blue-600 hover:border-blue-100 hover:shadow-xl hover:shadow-blue-900/5 transition-all flex items-center gap-3 active:scale-95">
                <i class="fa-solid fa-arrow-left text-sm"></i> <span>กลับหน้าหลัก</span>
            </a>
        </div>

        <div class="bg-white rounded-[3rem] shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] overflow-hidden flex chat-container border border-slate-100 flex-1">
            <!-- Sidebar: User List -->
            <div class="user-list flex flex-col bg-white">
                <div class="p-8 border-b border-slate-50">
                    <div class="relative group">
                        <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" placeholder="ค้นหาการสนทนา..."
                            class="w-full pl-13 pr-5 py-4 bg-slate-50 border-none rounded-2xl text-[13px] font-bold focus:ring-4 focus:ring-blue-50 transition-all placeholder:text-slate-300">
                    </div>
                </div>
                <div id="user-list-container" class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                    <!-- Loading State -->
                    <div class="p-20 text-center">
                        <div class="w-12 h-12 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Window -->
            <div id="chat-window-placeholder" class="chat-window items-center justify-center bg-slate-50/30">
                <div class="text-center max-w-xs">
                    <div class="w-24 h-24 bg-white rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-blue-900/10 border border-slate-50 relative">
                        <i class="fa-solid fa-comments text-4xl text-blue-500"></i>
                        <div class="absolute -right-2 -top-2 w-8 h-8 bg-emerald-400 rounded-full border-4 border-white animate-pulse"></div>
                    </div>
                    <h3 class="text-slate-900 font-black text-xl mb-2">เริ่มการสนทนา</h3>
                    <p class="text-slate-400 text-sm font-bold leading-relaxed">เลือกรายชื่อผู้ใช้งานทางด้านซ้าย<br>เพื่อตรวจสอบข้อความและตอบกลับ</p>
                </div>
            </div>

            <div id="chat-window-active" class="chat-window hidden">
                <!-- Chat Header -->
                <div class="px-10 py-6 border-b border-slate-50 flex items-center justify-between bg-white/80 backdrop-blur-xl relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="relative">
                            <img id="active-user-img" src=""
                                class="w-14 h-14 rounded-[1.5rem] object-cover border-2 border-white shadow-xl">
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-full border-4 border-white shadow-sm"></span>
                        </div>
                        <div>
                            <h3 id="active-user-name" class="text-slate-900 font-black text-lg leading-tight mb-1.5">...</h3>
                            <div class="flex items-center gap-2">
                                <span class="status-pulse"></span>
                                <span class="text-emerald-500 text-[10px] font-black uppercase tracking-[0.2em]">Online Now</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="w-11 h-11 bg-slate-50 rounded-xl text-slate-400 flex items-center justify-center hover:bg-slate-100 transition-all"><i class="fa-solid fa-phone"></i></button>
                        <button class="w-11 h-11 bg-slate-50 rounded-xl text-slate-400 flex items-center justify-center hover:bg-slate-100 transition-all"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>
                </div>

                <!-- Messages -->
                <div id="messages-container"
                    class="flex-1 overflow-y-auto p-10 space-y-8 bg-[#F8FAFF] flex flex-col custom-scrollbar relative">
                    <!-- Messages will be injected here -->
                </div>

                <!-- Input Area -->
                <div class="p-8 bg-white border-t border-slate-50 relative z-10">
                    <form id="chat-form" onsubmit="handleStaffSubmit(event)" class="relative flex items-center gap-4">
                        <div class="relative flex-1">
                            <input type="text" id="chat-input" placeholder="พิมพ์ข้อความตอบกลับ..."
                                class="w-full h-18 bg-slate-50 border-none rounded-[1.8rem] pl-8 pr-12 text-sm font-bold focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-slate-300">
                        </div>
                        <button type="submit"
                            class="w-18 h-18 bg-blue-600 text-white rounded-2xl font-black shadow-xl shadow-blue-600/30 active:scale-90 transition-all flex items-center justify-center overflow-hidden">
                            <i class="fa-solid fa-paper-plane text-xl"></i>
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
