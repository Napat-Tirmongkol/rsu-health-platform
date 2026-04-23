<?php
// includes/header.php
@session_start();

// ดึง Base Path ของ e_Borrow มาใช้เพื่อความแม่นยำของ Assets
$base_url = explode('/e_Borrow', $_SERVER['SCRIPT_NAME'])[0] . '/e_Borrow/';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo $base_url; ?>">

    <style>
        /* Smooth Page Transition */
        body {
            opacity: 1;
            transition: opacity 0.25s ease-out, transform 0.25s ease-out;
        }

        body.page-transitioning {
            opacity: 0;
            transform: translateY(10px);
        }
    </style>

    <title><?php echo isset($page_title) ? $page_title : 'ระบบยืม-คืนอุปกรณ์'; ?></title>

    <script>
        (function () {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) { console.error('Theme init error:', e); }
        })();
    </script>

    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
    <link rel="icon" href="data:,">
    <link rel="icon" type="image/png" href="assets/img/logo.png" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="page-transitioning">

    <script>
        window.addEventListener('DOMContentLoaded', () => document.body.classList.remove('page-transitioning'));
    </script>

    <header class="header">
        <h1>E-Borrow</h1>
        
        <a href="../portal/index.php"
                class="flex-1 sm:flex-none justify-center bg-white border border-gray-200 text-gray-800 px-5 py-2.5 rounded-xl font-bold text-sm hover:shadow-lg hover:border-gray-300 hover:text-blue-600 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
            </a>

        <div class="user-info">
            <div class="user-greeting">
                สวัสดี, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'ผู้ใช้'); ?></strong>
                (<?php
                $role = $_SESSION['role'] ?? 'viewer';
                if ($role == 'admin') {
                    echo '<span style="color: #ffc107; font-weight: bold;">Admin <i class="fa-solid fa-crown"></i></span>';
                } elseif ($role == 'employee') {
                    echo '<span style="color: #48c774;">Staff</span>';
                } else {
                    echo htmlspecialchars($role);
                }
                ?>)
            </div>

            <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" title="สลับโหมด">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
            </button>

            <a href="admin/logout.php" class="btn btn-logout" title="ออกจากระบบ">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span class="logout-text">ออกจากระบบ</span>
            </a>
        </div>
    </header>

    <main class="content" style="margin-top: 80px;">