<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;

class DisplayTimeNormalizer extends Constraint
{
    public $path = 'displayTime';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
