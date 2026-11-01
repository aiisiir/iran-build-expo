<?php

namespace App\Enums;

enum BadgeStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
