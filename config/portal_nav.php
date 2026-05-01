<?php

return [
    'sections' => [
        [
            'title' => 'Overview',
            'items' => [
                ['route' => 'portal.dashboard', 'icon' => 'fa-gauge-high',   'label' => 'KPI Dashboard'],
            ],
        ],
        [
            'title' => 'จัดการระบบ',
            'items' => [
                ['route' => 'portal.admins',        'icon' => 'fa-user-shield',  'label' => 'จัดการ Admin'],
                ['route' => 'portal.activity_logs', 'icon' => 'fa-list-check',   'label' => 'Activity Logs'],
            ],
        ],
        [
            'title' => 'ข้อมูล & ตั้งค่า',
            'items' => [
                ['route' => 'portal.clinic_data',  'icon' => 'fa-hospital',  'label' => 'ข้อมูลคลินิก'],
                ['route' => 'portal.maintenance',  'icon' => 'fa-gears',     'label' => 'Maintenance'],
            ],
        ],
    ],
];
