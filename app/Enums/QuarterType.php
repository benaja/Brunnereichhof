<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class QuarterType extends Enum
{
    const EmployerConfirmation = 0;
    const CreditToEichhof = 1;
    const FamilyAllowancesPaid = 2;
}
