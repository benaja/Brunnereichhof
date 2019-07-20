<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class WorkTypeEnum extends Enum
{
    const ProductiveHours = 1;
    const Holydays = 2;
    const Sick = 3;
    const Accident = 4;
    const Schoool = 5;
}
