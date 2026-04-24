<?php
/**
 * portal/_partials/insurance_sync.php — Insurance Sync Hub (Partial for SPA)
 */
declare(strict_types=1);

// Logic extracted from original insurance_sync.php
$pdo = db();

// KPI stats
$kpi = ['total' => 0, 'active' => 0, 'inactive' => 0, 'manual' => 0, 'last_sync' => null];
try {
    $row = $pdo->query("SELECT COUNT(*) as total,
        SUM(insurance_status='Active') as active,
        SUM(insurance_status='Inactive') as inactive,
        SUM(manually_overridden=1) as manual
        FROM insurance_members")->fetch(PDO::FETCH_ASSOC);
    $kpi = array_merge($kpi, $row ?? []);
    $kpi['last_sync'] = $pdo->query("SELECT synced_at, filename FROM insurance_sync_logs ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* silent */ }

// Sync history
$syncHistory = [];
try {
    $syncHistory = $pdo->query("
        SELECT l.id, l.filename, l.total_matched, l.total_inactivated, l.total_newcomers,
               l.total_active, l.synced_at, a.full_name AS synced_by_name
        FROM insurance_sync_logs l
        LEFT JOIN sys_admins a ON l.synced_by = a.id
        ORDER BY l.id DESC LIMIT 30
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* silent */ }

$csrfToken = get_csrf_token();
?>

<style>
    .ins-stat-card { background: #fff; border-radius: 20px; padding: 24px; border: 1.5px solid #e2e8f0; transition: transform .2s, box-shadow .2s; }
    .ins-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,.05); }
    .ins-tab-btn { transition: all .2s; border-radius: 12px; font-weight: 800; font-size: 13px; }
    .ins-tab-btn.active { background: #0052CC; color: #fff; box-shadow: 0 4px 12px rgba(0,82,204,.2); }
    .ins-tab-btn:not(.active) { background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; }
    .ins-tab-content { display: none; }
    .ins-tab-content.active { display: block; animation: insFadeIn .3s ease; }
    @keyframes insFadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
    
    .upload-area { border: 2.5px dashed #bfdbfe; background: #eff6ff; border-radius: 24px; cursor: pointer; transition: all .2s; }
    .upload-area:hover, .upload-area.drag-over { border-color: #0052CC; background: #dbeafe; transform: scale(1.01); }
    
    .ins-badge { padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
    .badge-active { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-inactive { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-manual { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
</style>

<div class="px-5 md:px-8 py-8 space-y-8">
    
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <div class="sec-title" style="margin-bottom:4px">🛡️ Insurance Sync Hub</div>
            <p style="font-size:13px;color:#64748b">จัดการข้อมูลสิทธิ์ประกันสุขภาพของบุคลากรและนักศึกษา</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Visibility Toggle -->
            <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex flex-col text-right">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Hub Visibility</span>
                    <span id="insVisibilityStatus" class="text-[10px] font-bold <?= defined('SITE_SHOW_INSURANCE') && SITE_SHOW_INSURANCE ? 'text-blue-600' : 'text-gray-400' ?> leading-none">
                        <?= defined('SITE_SHOW_INSURANCE') && SITE_SHOW_INSURANCE ? 'CARD ACTIVE' : 'HIDDEN' ?>
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="insToggleVisibility" class="sr-only peer" <?= defined('SITE_SHOW_INSURANCE') && SITE_SHOW_INSURANCE ? 'checked' : '' ?> onchange="updateInsVisibility(this)">
                    <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <a href="ajax_insurance_export.php?type=active" class="bg-emerald-600 text-white px-5 py-2.5 rounded-2xl text-xs font-black hover:opacity-90 transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                <i class="fa-solid fa-file-arrow-down"></i> Export Active List
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="ins-stat-card">
            <div class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Total Members</div>
            <div class="text-3xl font-black text-slate-900"><?= number_format((int)$kpi['total']) ?></div>
            <div class="mt-2 text-[10px] text-gray-400 font-bold">ฐานข้อมูลปัจจุบัน</div>
        </div>
        <div class="ins-stat-card">
            <div class="text-[10px] font-black text-emerald-600/60 uppercase tracking-[0.2em] mb-2">Active Rights</div>
            <div class="text-3xl font-black text-emerald-600"><?= number_format((int)$kpi['active']) ?></div>
            <div class="mt-2 text-[10px] text-emerald-500 font-bold">มีสิทธิ์การรักษา</div>
        </div>
        <div class="ins-stat-card">
            <div class="text-[10px] font-black text-rose-500/60 uppercase tracking-[0.2em] mb-2">Inactive</div>
            <div class="text-3xl font-black text-rose-500"><?= number_format((int)$kpi['inactive']) ?></div>
            <div class="mt-2 text-[10px] text-rose-400 font-bold">สิทธิ์หมดอายุ/ไม่พบ</div>
        </div>
        <div class="ins-stat-card">
            <div class="text-[10px] font-black text-amber-500/60 uppercase tracking-[0.2em] mb-2">Manual Override</div>
            <div class="text-3xl font-black text-amber-500"><?= number_format((int)$kpi['manual']) ?></div>
            <div class="mt-2 text-[10px] text-amber-400 font-bold">ล็อคสิทธิ์ด้วยมือ</div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        
        <!-- Tabs Header -->
        <div class="px-8 pt-6 pb-2 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button class="ins-tab-btn active px-6 py-3" onclick="switchInsTab('sync', this)">
                <i class="fa-solid fa-rotate mr-2"></i>Sync Data
            </button>
            <button class="ins-tab-btn px-6 py-3" onclick="switchInsTab('history', this)">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i>Sync History
            </button>
            <button class="ins-tab-btn px-6 py-3" onclick="switchInsTab('members', this)">
                <i class="fa-solid fa-users mr-2"></i>Member Directory
            </button>
        </div>

        <div class="p-8">
            <!-- ─────────────────────── TAB: SYNC ─────────────────────────────── -->
            <div id="ins-tab-sync" class="ins-tab-content active">
                <div class="max-w-4xl mx-auto space-y-8">
                    <div class="text-center">
                        <h2 class="text-xl font-black text-slate-800 mb-2">อัปเดตสิทธิ์ประกันภัย</h2>
                        <p class="text-sm text-slate-400">กรุณาเลือกไฟล์ CSV หรือ Excel จากบริษัทประกัน และทะเบียนบุคลากร</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Insurance File -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">1. Insurance File</label>
                                <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded">REQUIRED</span>
                            </div>
                            <div class="upload-area flex flex-col items-center justify-center py-12 px-6" id="insAreaP" onclick="document.getElementById('insFileP').click()">
                                <div class="w-16 h-16 bg-white rounded-3xl shadow-lg flex items-center justify-center text-blue-600 text-2xl mb-4 border border-blue-50">
                                    <i class="fa-solid fa-file-shield"></i>
                                </div>
                                <p class="text-sm font-black text-slate-700" id="insFileLabelP">คลิกเพื่อเลือกไฟล์ประกัน</p>
                                <p class="text-[11px] text-slate-400 mt-1">.csv, .xlsx, .xls</p>
                            </div>
                            <input type="file" id="insFileP" accept=".csv,.xlsx,.xls" class="hidden" onchange="handleInsFileChange(this)">
                        </div>

                        <!-- Staff File -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">2. Staff Directory</label>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded">OPTIONAL</span>
                            </div>
                            <div class="upload-area flex flex-col items-center justify-center py-12 px-6 border-slate-200 bg-slate-50/50" id="regAreaP" onclick="document.getElementById('regFileP').click()">
                                <div class="w-16 h-16 bg-white rounded-3xl shadow-lg flex items-center justify-center text-slate-400 text-2xl mb-4 border border-slate-50">
                                    <i class="fa-solid fa-address-book"></i>
                                </div>
                                <p class="text-sm font-black text-slate-500" id="regFileLabelP">ไฟล์ทะเบียนบุคลากร</p>
                                <p class="text-[11px] text-slate-400 mt-1">ใช้เพื่ออัปเดตชื่อ/ตำแหน่ง</p>
                            </div>
                            <input type="file" id="regFileP" accept=".csv,.xlsx,.xls" class="hidden" onchange="handleRegFileChange(this)">
                        </div>
                    </div>

                    <button id="insBtnDryRun" onclick="doInsDryRun()" disabled
                        class="w-full h-16 bg-[#0052CC] text-white font-black rounded-2xl shadow-xl shadow-blue-200 active:scale-95 transition-all flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-magnifying-glass"></i> เริ่มการตรวจสอบข้อมูล (Dry Run)
                    </button>

                    <!-- Dry Run Results Placeholder -->
                    <div id="insDryResult" class="hidden space-y-6 pt-4">
                        <!-- Summary and Tables will be injected here -->
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB: HISTORY ─────────────────────────────── -->
            <div id="ins-tab-history" class="ins-tab-content">
                <?php if (empty($syncHistory)): ?>
                    <div class="text-center py-20">
                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-4 text-slate-200">
                            <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
                        </div>
                        <p class="text-slate-400 font-bold">ยังไม่มีประวัติการซิงค์ข้อมูล</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80">
                                    <th class="text-left px-6 py-4">ID</th>
                                    <th class="text-left px-6 py-4">Filename</th>
                                    <th class="text-center px-6 py-4 text-emerald-600">Matched</th>
                                    <th class="text-center px-6 py-4 text-blue-600">New</th>
                                    <th class="text-center px-6 py-4 text-rose-500">Inact.</th>
                                    <th class="text-center px-6 py-4">Result</th>
                                    <th class="text-right px-6 py-4">Timestamp</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($syncHistory as $log): ?>
                                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                                        <td class="px-6 py-4 font-black text-slate-400 text-xs">#<?= $log['id'] ?></td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($log['filename']) ?></div>
                                            <div class="text-[10px] text-slate-400 font-bold">By: <?= htmlspecialchars($log['synced_by_name'] ?? 'System') ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-black text-emerald-600"><?= number_format($log['total_matched']) ?></td>
                                        <td class="px-6 py-4 text-center font-black text-blue-600"><?= number_format($log['total_newcomers']) ?></td>
                                        <td class="px-6 py-4 text-center font-black text-rose-500"><?= number_format($log['total_inactivated']) ?></td>
                                        <td class="px-6 py-4 text-center font-black text-slate-700"><?= number_format($log['total_active']) ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-xs font-bold text-slate-800"><?= date('d M Y', strtotime($log['synced_at'])) ?></div>
                                            <div class="text-[10px] text-slate-400 font-bold"><?= date('H:i', strtotime($log['synced_at'])) ?> น.</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="viewInsSyncDetail(<?= $log['id'] ?>)" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ─────────────────────── TAB: MEMBERS ─────────────────────────────── -->
            <div id="ins-tab-members" class="ins-tab-content">
                <div class="space-y-6">
                    <!-- Search Bar -->
                    <div class="flex flex-wrap gap-4 items-center bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        <div class="relative flex-1 min-w-[240px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" id="insMemberSearch" placeholder="ค้นหา รหัสสมาชิก, ชื่อ-นามสกุล หรือ เลขบัตรประชาชน..." 
                                class="w-full h-14 pl-12 pr-6 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                        <select id="insMemberFilter" class="h-14 px-6 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none">
                            <option value="all">สถานะทั้งหมด</option>
                            <option value="Active">Active Only</option>
                            <option value="Inactive">Inactive Only</option>
                        </select>
                        <button onclick="loadInsMembers(1)" class="h-14 px-8 bg-slate-900 text-white rounded-2xl font-black text-sm active:scale-95 transition-all shadow-lg">
                            ค้นหาข้อมูล
                        </button>
                    </div>

                    <div id="insMembersResult" class="overflow-x-auto min-h-[300px]">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for Insurance Sync (Moved from original file and refined for SPA) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
(function() {
    const CSRF = '<?= $csrfToken ?>';
    let dryRunData = null;

    // --- Tab Switching ---
    window.switchInsTab = function(name, btn) {
        document.querySelectorAll('.ins-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.ins-tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById('ins-tab-' + name).classList.add('active');
        btn.classList.add('active');
        
        if (name === 'members') loadInsMembers(1);
    };

    // --- File Handling ---
    window.handleInsFileChange = function(input) {
        const label = document.getElementById('insFileLabelP');
        const area = document.getElementById('insAreaP');
        if (input.files[0]) {
            label.textContent = input.files[0].name;
            area.classList.add('border-blue-500', 'bg-blue-50');
            document.getElementById('insBtnDryRun').disabled = false;
        } else {
            label.textContent = 'คลิกเพื่อเลือกไฟล์ประกัน';
            area.classList.remove('border-blue-500', 'bg-blue-50');
            document.getElementById('insBtnDryRun').disabled = true;
        }
    };

    window.handleRegFileChange = function(input) {
        const label = document.getElementById('regFileLabelP');
        const area = document.getElementById('regAreaP');
        if (input.files[0]) {
            label.textContent = input.files[0].name;
            area.classList.add('border-emerald-500', 'bg-emerald-50');
        } else {
            label.textContent = 'ไฟล์ทะเบียนบุคลากร';
            area.classList.remove('border-emerald-500', 'bg-emerald-50');
        }
    };

    // --- Helper: Convert Excel to CSV Blob ---
    async function excelToCSV(file) {
        if (!/\.(xlsx|xls)$/i.test(file.name)) return file;
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const wb = XLSX.read(data, { type: 'array', cellDates: true });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const csv = XLSX.utils.sheet_to_csv(ws, { blankrows: false });
                    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                    resolve(new File([blob], file.name.replace(/\.(xlsx|xls)$/i, '.csv'), { type: 'text/csv' }));
                } catch (err) { reject(err); }
            };
            reader.readAsArrayBuffer(file);
        });
    }

    // --- Dry Run ---
    window.doInsDryRun = async function() {
        const btn = document.getElementById('insBtnDryRun');
        const insFileRaw = document.getElementById('insFileP').files[0];
        const regFileRaw = document.getElementById('regFileP').files[0];

        if (!insFileRaw) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>กำลังตรวจสอบข้อมูล...';

        try {
            const insFile = await excelToCSV(insFileRaw);
            const regFile = regFileRaw ? await excelToCSV(regFileRaw) : null;

            const fd = new FormData();
            fd.append('action', 'dryrun');
            fd.append('csrf_token', CSRF);
            fd.append('insurance_file', insFile);
            if (regFile) fd.append('registry_file', regFile);

            const res = await fetch('ajax_insurance_sync.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status !== 'ok') {
                Swal.fire('ข้อผิดพลาด', data.message, 'error');
                return;
            }

            dryRunData = data;
            renderInsDryResult(data);
        } catch (err) {
            Swal.fire('Error', 'ไม่สามารถตรวจสอบข้อมูลได้: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass mr-2"></i>เริ่มการตรวจสอบข้อมูล (Dry Run)';
        }
    };

    function renderInsDryResult(data) {
        const resultDiv = document.getElementById('insDryResult');
        resultDiv.classList.remove('hidden');
        
        const totalInact = data.total_will_inactivate ?? data.total_inactivated;
        
        let html = `
            <div class="bg-slate-50 rounded-[2rem] p-8 border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-black text-slate-800">ผลการตรวจสอบล่วงหน้า</h3>
                    <div class="flex gap-2">
                        <span class="ins-badge badge-active">Matched: ${data.total_matched}</span>
                        <span class="ins-badge bg-blue-50 text-blue-600 border border-blue-100">New: ${data.total_newcomers}</span>
                        <span class="ins-badge badge-inactive">Inactivate: ${totalInact}</span>
                    </div>
                </div>

                ${data.guard_triggered ? `
                    <div class="bg-rose-50 border border-rose-100 rounded-2xl p-6 mb-8 flex items-start gap-4">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-rose-600 shrink-0 shadow-sm border border-rose-50">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h4 class="text-rose-900 font-black text-sm mb-1">คำเตือน: อัตราการ Inactivate สูงผิดปกติ</h4>
                            <p class="text-rose-700 text-xs font-bold leading-relaxed">ตรวจพบการระงับสิทธิ์จำนวน ${totalInact} ราย (${data.guard_percent}% ของทั้งหมด) กรุณาตรวจสอบความถูกต้องของไฟล์ หากมั่นใจแล้วให้เลือก "บังคับยืนยัน"</p>
                        </div>
                    </div>
                ` : ''}

                <div class="flex gap-4">
                    <button onclick="execInsSync(${data.guard_triggered ? 'true' : 'false'})" 
                        class="flex-1 h-16 ${data.guard_triggered ? 'bg-rose-600 shadow-rose-200' : 'bg-emerald-600 shadow-emerald-200'} text-white font-black rounded-2xl shadow-xl active:scale-95 transition-all flex items-center justify-center gap-3">
                        <i class="fa-solid fa-check-double"></i> ${data.guard_triggered ? 'บังคับยืนยัน (Override Guard)' : 'ยืนยันและอัปเดตข้อมูล'}
                    </button>
                    <button onclick="document.getElementById('insDryResult').classList.add('hidden')" class="px-8 h-16 bg-white border border-slate-200 text-slate-400 font-black rounded-2xl active:scale-95 transition-all">ยกเลิก</button>
                </div>
            </div>
        `;
        resultDiv.innerHTML = html;
        resultDiv.scrollIntoView({ behavior: 'smooth' });
    }

    window.execInsSync = async function(force) {
        if (!dryRunData) return;
        
        const confirm = await Swal.fire({
            title: 'ยืนยันการ Sync?',
            text: 'ข้อมูลในฐานข้อมูลจะถูกปรับปรุงทันที ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: force ? '#dc2626' : '#10b981',
            confirmButtonText: 'ยืนยันดำเนินการ'
        });

        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'กำลังประมวลผล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const fd = new FormData();
        fd.append('action', 'execute');
        fd.append('csrf_token', CSRF);
        fd.append('insurance_b64', dryRunData.insurance_b64);
        fd.append('registry_b64', dryRunData.registry_b64 || '');
        fd.append('ins_filename', dryRunData.ins_filename);
        fd.append('reg_filename', dryRunData.reg_filename || '');
        fd.append('force_override', force ? '1' : '0');

        try {
            const res = await fetch('ajax_insurance_sync.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.status === 'success') {
                Swal.fire('สำเร็จ!', result.message, 'success').then(() => switchSection('insurance_sync'));
            } else {
                Swal.fire('ล้มเหลว', result.message, 'error');
            }
        } catch (err) { Swal.fire('Error', 'Connection failed', 'error'); }
    };

    window.loadInsMembers = async function(page) {
        const container = document.getElementById('insMembersResult');
        const q = document.getElementById('insMemberSearch').value;
        const f = document.getElementById('insMemberFilter').value;
        
        container.innerHTML = '<div class="flex items-center justify-center py-20"><i class="fa-solid fa-spinner fa-spin text-4xl text-blue-200"></i></div>';

        const fd = new FormData();
        fd.append('action', 'list_members');
        fd.append('csrf_token', CSRF);
        fd.append('page', page);
        fd.append('search', q);
        fd.append('filter', f);

        try {
            const res = await fetch('ajax_insurance_sync.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status !== 'ok') throw new Error(data.message);

            if (!data.members.length) {
                container.innerHTML = '<div class="text-center py-20 text-slate-300 font-bold">ไม่พบข้อมูลสมาชิก</div>';
                return;
            }

            let html = `
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80">
                            <th class="text-left px-6 py-4">Member ID</th>
                            <th class="text-left px-6 py-4">Name / Position</th>
                            <th class="text-center px-6 py-4">Status</th>
                            <th class="text-center px-6 py-4">Manual</th>
                            <th class="text-right px-6 py-4">Last Updated</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.members.map(m => `
                            <tr class="hover:bg-slate-50 border-b border-slate-100">
                                <td class="px-6 py-4 font-mono text-xs font-black text-slate-400">${m.member_id}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-black text-slate-800">${m.full_name}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">${m.position || '-'}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="ins-badge badge-${m.insurance_status === 'Active' ? 'active' : 'inactive'}">${m.insurance_status}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    ${m.manually_overridden == 1 ? '<span class="ins-badge badge-manual">LOCK</span>' : '<span class="text-slate-200 text-xs">—</span>'}
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-slate-400">
                                    ${m.updated_at ? m.updated_at.substring(0,16) : '-'}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="openInsOverrideModal('${m.member_id}', '${m.full_name.replace(/'/g, "\\'")}', '${m.insurance_status}')" class="text-blue-600 hover:underline text-xs font-black">EDIT</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            container.innerHTML = html;
        } catch (err) { container.innerHTML = '<p class="text-rose-500 font-bold p-8">Error: ' + err.message + '</p>'; }
    };

    window.updateInsVisibility = function(cb) {
        const fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('action', 'update_visibility');
        fd.append('visible', cb.checked ? '1' : '0');
        
        fetch('ajax_insurance_sync.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                const status = document.getElementById('insVisibilityStatus');
                if (data.ok) {
                    status.textContent = cb.checked ? 'CARD ACTIVE' : 'HIDDEN';
                    status.className = `text-[10px] font-bold ${cb.checked ? 'text-blue-600' : 'text-gray-400'} leading-none`;
                }
            });
    };

    function esc(s) { return s ? s.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }

})();
</script>
