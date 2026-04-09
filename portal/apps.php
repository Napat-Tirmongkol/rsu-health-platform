<?php
/**
 * portal/apps.php
 * Application URLs
 */
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

$adminRole = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application URLs - Management Smart Portal</title>
    <!-- UI Framework & Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Prompt:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="../assets/css/portal.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../archive/e_Borrow/assets/img/logo.png">
</head>
<body class="font-sans text-gray-800" style="min-height:100vh">

    <!-- Ambient background dots -->
    <div class="amb-dot" style="width:320px;height:320px;background:rgba(46,158,99,.1);top:5%;left:10%;--dur:16s;--delay:0s;--dx:40px;--dy:-30px"></div>
    <div class="amb-dot" style="width:240px;height:240px;background:rgba(77,201,138,.07);top:60%;right:8%;--dur:20s;--delay:-5s;--dx:-35px;--dy:25px"></div>

    <!-- HEADER (Simplified from Index) -->
    <header class="portal-header au">
        <div class="max-w-[1280px] mx-auto px-6 py-3 flex items-center justify-between gap-4">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <a href="index.php" class="brand-icon hover:scale-110 transition-transform"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <div class="font-black text-gray-900 text-[17px] leading-none tracking-tight">Application URLs</div>
                    <div class="text-[10px] font-bold tracking-[.15em] uppercase opacity-70 mt-0.5" style="color:#2e9e63">Copy & Share Center</div>
                </div>
            </div>
            <!-- Right: user -->
            <div class="flex items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar"><i class="fa-solid fa-user-shield text-[11px]"></i></div>
                    <div class="hidden sm:block">
                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none mb-0.5">Admin</div>
                        <div class="text-xs font-black text-gray-900 leading-none">
                            <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- PAGE BODY -->
    <div class="max-w-[800px] mx-auto px-5 md:px-8 py-12 space-y-8 au d1">
        
        <div class="shortcut-card" style="position: relative; z-index: 10;">
            <div class="text-xs font-black uppercase tracking-widest opacity-70 mb-1">Global Directory</div>
            <div class="font-black text-3xl mb-8">System Access URLs</div>
            
            <div class="space-y-4">
                <?php 
                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['PHP_SELF'], 2); // ถอยกลับ 1 ระดับจาก portal/
                $sys_base = rtrim($base_url . $path, '/');
                
                $appUrls = [
                    ['icon' => 'fa-hospital-user', 'label' => 'หน้าเว็บ E-Campaign สำหรับลูกค้า (Landing Page)', 'url' => $sys_base . '/'],
                    ['icon' => 'fa-box-open', 'label' => 'ระบบ E-Borrow ค้นหาและทำรายการยืม-คืน', 'url' => $sys_base . '/archive/e_Borrow/'],
                    ['icon' => 'fa-shield-halved', 'label' => 'หน้า Login ทางเข้าสำหรับแอดมิน / เจ้าหน้าที่พยาบาล', 'url' => $sys_base . '/login.php'],
                ];
                foreach($appUrls as $app):
                ?>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 bg-white/10 rounded-xl p-5 hover:bg-white/20 transition-all group border border-white/5 shadow-md">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-black/25 flex items-center justify-center shrink-0">
                            <i class="fa-solid <?= $app['icon'] ?> text-white/90 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[15px] font-bold text-white mb-2 tracking-wide"><?= htmlspecialchars($app['label']) ?></div>
                            <input type="text" value="<?= htmlspecialchars($app['url']) ?>" readonly 
                                class="bg-black/30 border border-white/10 rounded-lg text-[14px] font-mono text-green-300 outline-none cursor-text w-full truncate px-3 py-2 select-all focus:border-green-400 focus:bg-black/50 transition-colors" title="<?= htmlspecialchars($app['url']) ?>">
                        </div>
                    </div>
                    <button onclick="copyAppUrl(this, '<?= htmlspecialchars($app['url']) ?>')" title="Copy URL"
                        class="h-11 px-6 flex items-center justify-center gap-2 bg-white text-[#2e9e63] hover:bg-[#2e9e63] hover:text-white font-black rounded-lg transition-all shadow-md shrink-0 sm:self-end shadow-black/20">
                        <i class="fa-regular fa-copy text-sm"></i> <span>Copy</span>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <i class="fa-solid fa-link absolute -bottom-10 right-0 text-[12rem] opacity-[0.03] rotate-12 pointer-events-none"></i>
        </div>

    </div>

    <!-- FOOTER -->
    <footer class="pt-10 pb-4 text-center">
        <div class="flex items-center justify-center gap-2 opacity-25">
            <i class="fa-solid fa-shield-halved" style="color:#2e9e63"></i>
            <span class="text-[10px] font-black uppercase tracking-[.4em]">Central Command v3.0 · RSU Healthcare</span>
        </div>
    </footer>

    <script>
        window.copyAppUrl = function(btn, txt) {
            navigator.clipboard.writeText(txt).then(() => {
                const icon = btn.querySelector('i');
                const textSpan = btn.querySelector('span');
                const origClass = icon.className;
                
                icon.className = 'fa-solid fa-check text-sm';
                textSpan.innerText = 'Copied!';
                btn.classList.add('!bg-green-500', '!text-white');
                
                setTimeout(() => {
                    icon.className = origClass;
                    textSpan.innerText = 'Copy';
                    btn.classList.remove('!bg-green-500', '!text-white');
                }, 1500);
            });
        };
    </script>
</body>
</html>
