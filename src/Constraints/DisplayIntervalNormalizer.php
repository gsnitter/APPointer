<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;

class DisplayIntervalNormalizer extends Constraint
{
    public $path = 'displayInterval';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
