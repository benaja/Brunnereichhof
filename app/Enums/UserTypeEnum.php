<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserTypeEnum extends Enum
{
    const Customer = 1;
    const Worker = 2;
    const SuperAdmin = 3;
    const Employee = 4;
}
