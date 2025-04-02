<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ScheduleDesludging extends Enum
{
    const confirm =   0;
    const disagree =   1;
    const finaldisagree = 2;
    const reschedule =   3;
   
}
