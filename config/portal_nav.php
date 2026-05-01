<?php

return [
    'sections' => [
        [
            'title' => 'Overview',
            'items' => [
                ['route' => 'portal.dashboard', 'icon' => 'fa-gauge-high', 'label' => 'Dashboard'],
            ],
        ],
        [
            'title' => 'Clinic Management',
            'items' => [
                ['route' => 'portal.clinics',      'icon' => 'fa-hospital',      'label' => 'Clinics'],
                ['route' => 'portal.admins',        'icon' => 'fa-user-shield',   'label' => 'Admins'],
                ['route' => 'portal.activity_logs', 'icon' => 'fa-list-check',    'label' => 'Activity Logs'],
            ],
        ],
        [
            'title' => 'Settings',
            'items' => [
                ['route' => 'portal.clinic_data',        'icon' => 'fa-sliders',      'label' => 'Per-Clinic Settings'],
                ['route' => 'portal.settings',           'icon' => 'fa-gear',         'label' => 'Global Settings'],
                ['route' => 'portal.maintenance',        'icon' => 'fa-gears',        'label' => 'Maintenance'],
            ],
        ],
        [
            'title' => 'Chatbot',
            'items' => [
                ['route' => 'portal.chatbot.faqs',     'icon' => 'fa-comments',     'label' => 'FAQ Manager'],
                ['route' => 'portal.chatbot.settings', 'icon' => 'fa-robot',        'label' => 'Chatbot Settings'],
            ],
        ],
    ],
];
