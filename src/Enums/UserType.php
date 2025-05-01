<?php

declare(strict_types=1);

namespace App\CommissionTask\Enums;

enum UserType: string
{
    case Private = 'private';
    case Business = 'business';
}
