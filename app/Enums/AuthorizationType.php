<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AuthorizationType extends Enum
{
    const Customer = 1;
    const Admin = 2;
    const Worker = 3;
    const SuperAdmin = 4;
}
