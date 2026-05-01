<?php

namespace App\Enums;

enum ChatIntent: string
{
    case GREETING = 'greeting';
    case FAQ = 'faq';
    case SYMPTOM = 'symptom';
    case BOOKING = 'booking';
    case EMERGENCY = 'emergency';
    case FALLBACK = 'fallback';
}
