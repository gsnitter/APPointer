<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;

class DateStringNormalizer extends Constraint
{
    public $path = 'dateString';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
