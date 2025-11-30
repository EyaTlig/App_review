<?php

namespace App\Enum;

enum RoleType: string
{
    case ADMIN = 'ADMIN';
    case CUSTOMER = 'CUSTOMER';
    case SERVICE_OWNER = 'SERVICE_OWNER';
}
