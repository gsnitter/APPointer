<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;

class AlarmTimesNormalizer extends Constraint
{
    public $path = 'alarmTimes';
    public $dateGetter = 'getDate';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
