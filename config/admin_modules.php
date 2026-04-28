<?php

return [
    'modules' => [
        'campaign' => [
            'name' => 'e-Campaign',
            'label' => 'บริการคลินิก',
            'description' => 'จัดการแคมเปญ การจอง และงานบริการของคลินิกในที่เดียว',
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
            'description' => 'ดูแลคำขอยืม สต็อก การคืนอุปกรณ์ และค่าปรับ',
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
    'sections' => [
        'platform' => [
            [
                'title' => 'พื้นที่ทำงาน',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'หน้าหลักแพลตฟอร์ม', 'icon' => 'fa-table-cells-large'],
                    ['route' => 'admin.workspace.campaign', 'label' => 'บริการคลินิก', 'icon' => 'fa-calendar-check'],
                    ['route' => 'admin.workspace.borrow', 'label' => 'ยืมอุปกรณ์และคลัง', 'icon' => 'fa-box-open'],
                ],
            ],
            [
                'title' => 'การดูแลระบบส่วนกลาง',
                'items' => [
                    ['route' => 'admin.system_admins', 'label' => 'ผู้ดูแลระบบ', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'บันทึกกิจกรรม', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
        'campaign' => [
            [
                'title' => 'งานคลินิก',
                'items' => [
                    ['route' => 'admin.workspace.campaign', 'label' => 'หน้าหลักโมดูล', 'icon' => 'fa-compass'],
                    ['route' => 'admin.campaigns', 'label' => 'แคมเปญ', 'icon' => 'fa-calendar-check'],
                    ['route' => 'admin.bookings', 'label' => 'รายการจอง', 'icon' => 'fa-users-viewfinder'],
                    ['route' => 'admin.time_slots', 'label' => 'รอบเวลา', 'icon' => 'fa-clock-rotate-left'],
                    ['route' => 'admin.users', 'label' => 'ประวัติผู้ใช้', 'icon' => 'fa-id-card'],
                    ['route' => 'admin.reports', 'label' => 'รายงาน', 'icon' => 'fa-chart-line'],
                ],
            ],
            [
                'title' => 'การดูแลระบบส่วนกลาง',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'หน้าหลักแพลตฟอร์ม', 'icon' => 'fa-table-cells-large'],
                    ['route' => 'admin.system_admins', 'label' => 'ผู้ดูแลระบบ', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'บันทึกกิจกรรม', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
        'borrow' => [
            [
                'title' => 'งานยืมอุปกรณ์',
                'items' => [
                    ['route' => 'admin.workspace.borrow', 'label' => 'หน้าหลักโมดูล', 'icon' => 'fa-compass'],
                    ['route' => 'admin.borrow_requests', 'label' => 'คำขอยืม', 'icon' => 'fa-box-open'],
                    ['route' => 'admin.inventory', 'label' => 'คลังอุปกรณ์', 'icon' => 'fa-boxes-stacked'],
                    ['route' => 'admin.borrow_returns', 'label' => 'รับคืนอุปกรณ์', 'icon' => 'fa-rotate-left'],
                    ['route' => 'admin.borrow_fines', 'label' => 'ค่าปรับและการชำระเงิน', 'icon' => 'fa-file-invoice-dollar'],
                    ['route' => 'admin.walk_in_borrow', 'label' => 'ยืมหน้าเคาน์เตอร์', 'icon' => 'fa-cart-plus'],
                ],
            ],
            [
                'title' => 'การดูแลระบบส่วนกลาง',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'หน้าหลักแพลตฟอร์ม', 'icon' => 'fa-table-cells-large'],
                    ['route' => 'admin.system_admins', 'label' => 'ผู้ดูแลระบบ', 'icon' => 'fa-user-shield'],
                    ['route' => 'admin.system_settings', 'label' => 'Integration Settings', 'icon' => 'fa-sliders'],
                    ['route' => 'admin.manage_staff', 'label' => 'ทีมเจ้าหน้าที่', 'icon' => 'fa-user-gear'],
                    ['route' => 'admin.activity_logs', 'label' => 'บันทึกกิจกรรม', 'icon' => 'fa-list-check'],
                ],
            ],
        ],
    ],
];
