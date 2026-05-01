<?php

return [
    'modules' => [
        'campaign' => [
            'name' => 'e-Campaign',
            'label' => 'บริการคลินิก',
            'description' => 'จัดการแคมเปญ การจอง การเช็กอิน และงานบริการผู้ป่วยในบริบทเดียว',
            'route' => 'admin.workspace.campaign',
            'icon' => 'fa-calendar-check',
            'patterns' => [
                'admin.workspace.campaign',
                'admin.campaigns',
                'admin.bookings',
                'admin.time_slots',
                'admin.users',
                'admin.reports',
            ],
        ],
        'borrow' => [
            'name' => 'e-Borrow',
            'label' => 'ยืมอุปกรณ์และคลัง',
            'description' => 'ดูคำขอยืม สต็อก การคืน ค่าปรับ และงานหน้าจุดบริการในที่เดียว',
            'route' => 'admin.workspace.borrow',
            'icon' => 'fa-box-open',
            'patterns' => [
                'admin.workspace.borrow',
                'admin.borrow_requests',
                'admin.inventory',
                'admin.borrow_returns',
                'admin.borrow_fines',
                'admin.walk_in_borrow',
                'admin.borrow_payments.receipt',
            ],
        ],
    ],
    'actions' => [
        'campaign' => [
            'label' => 'สิทธิ์ฝั่ง e-Campaign',
            'actions' => [
                [
                    'key' => 'campaign.manage',
                    'label' => 'จัดการแคมเปญและรอบเวลา',
                    'description' => 'เข้าถึงแคมเปญ รอบเวลา รายงาน และประวัติผู้รับบริการของคลินิก',
                ],
                [
                    'key' => 'campaign.booking.manage',
                    'label' => 'จัดการรายการจอง',
                    'description' => 'อนุมัติ ยกเลิก หรือดูแลรายการจองของผู้รับบริการ',
                ],
            ],
        ],
        'borrow' => [
            'label' => 'สิทธิ์ฝั่ง e-Borrow',
            'actions' => [
                [
                    'key' => 'borrow.request.approve',
                    'label' => 'อนุมัติคำขอยืม',
                    'description' => 'พิจารณาอนุมัติหรือปฏิเสธคำขอยืมอุปกรณ์',
                ],
                [
                    'key' => 'borrow.inventory.manage',
                    'label' => 'จัดการคลังและจุดบริการ',
                    'description' => 'เพิ่มแก้ไขอุปกรณ์ จัดการหมวดหมู่ และสร้างรายการยืมแบบ walk-in',
                ],
                [
                    'key' => 'borrow.return.process',
                    'label' => 'รับคืนอุปกรณ์',
                    'description' => 'บันทึกการคืน คำนวณค่าปรับ และปิดรายการยืม',
                ],
                [
                    'key' => 'borrow.fine.collect',
                    'label' => 'จัดเก็บค่าปรับ',
                    'description' => 'บันทึกรับชำระค่าปรับและออกใบเสร็จ',
                ],
            ],
        ],
    ],
    'sections' => [
        'platform' => [
            [
                'title' => 'ภาพรวมระบบ',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'หน้าหลักแพลตฟอร์ม', 'icon' => 'fa-house'],
                    ['route' => 'admin.workspace.campaign', 'label' => 'บริการคลินิก', 'icon' => 'fa-calendar-check'],
                    ['route' => 'admin.workspace.borrow', 'label' => 'ยืมอุปกรณ์และคลัง', 'icon' => 'fa-box-open'],
                ],
            ],
            [
                'title' => 'ส่วนควบคุมกลาง',
                'items' => [
                    ['route' => 'admin.system_admins', 'label' => 'System Admins', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'Activity Logs', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
        'campaign' => [
            [
                'title' => 'บริการคลินิก',
                'items' => [
                    ['route' => 'admin.workspace.campaign', 'label' => 'ภาพรวม workspace', 'icon' => 'fa-house'],
                    ['route' => 'admin.campaigns', 'label' => 'Campaigns', 'icon' => 'fa-syringe'],
                    ['route' => 'admin.bookings', 'label' => 'Bookings', 'icon' => 'fa-calendar-days'],
                    ['route' => 'admin.time_slots', 'label' => 'Time Slots', 'icon' => 'fa-clock'],
                    ['route' => 'admin.users', 'label' => 'User History', 'icon' => 'fa-id-card'],
                    ['route' => 'admin.reports', 'label' => 'Reports', 'icon' => 'fa-chart-line'],
                ],
            ],
            [
                'title' => 'เมนูส่วนกลาง',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'Platform Home', 'icon' => 'fa-table-cells-large'],
                    ['route' => 'admin.system_admins', 'label' => 'System Admins', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'Activity Logs', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
        'borrow' => [
            [
                'title' => 'ยืมอุปกรณ์และคลัง',
                'items' => [
                    ['route' => 'admin.workspace.borrow', 'label' => 'ภาพรวม workspace', 'icon' => 'fa-house'],
                    ['route' => 'admin.borrow_requests', 'label' => 'Borrow Requests', 'icon' => 'fa-inbox'],
                    ['route' => 'admin.inventory', 'label' => 'Inventory', 'icon' => 'fa-boxes-stacked'],
                    ['route' => 'admin.borrow_returns', 'label' => 'Returns', 'icon' => 'fa-rotate-left'],
                    ['route' => 'admin.borrow_fines', 'label' => 'Fines & Payments', 'icon' => 'fa-file-invoice-dollar'],
                    ['route' => 'admin.walk_in_borrow', 'label' => 'Walk-In Borrow', 'icon' => 'fa-cart-plus'],
                ],
            ],
            [
                'title' => 'เมนูส่วนกลาง',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'Platform Home', 'icon' => 'fa-table-cells-large'],
                    ['route' => 'admin.system_admins', 'label' => 'System Admins', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'Activity Logs', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
    ],
];
