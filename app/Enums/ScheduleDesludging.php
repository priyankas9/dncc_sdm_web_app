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
    const confirmedschedule =   1;
    const rescheduled = 2;
    const emptiedscheduled =   3;
    const rejectedonce =   4;
    const rejectedtwice =   5;

   
}
