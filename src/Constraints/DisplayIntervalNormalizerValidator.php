<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use APPointer\Parser\DisplayIntervalParser;

class DisplayIntervalNormalizerValidator extends ConstraintValidator
{
    public function __construct(DisplayIntervalParser $parser)
    {
        $this->parser = $parser;
    }

    public function validate($object, Constraint $constraint)
    {
        $getter = 'get' . ucfirst($constraint->path) . 'String';
        $setter = 'set' . ucfirst($constraint->path);

        if (!method_exists($object, $getter)) {
            $this->context->buildViolation("No getter function {$getter} found.")
                ->atPath($constraint->path)
                ->addViolation();
        }

        $displayString = $object->$getter();
        if (!$displayString) {
            return;
        }

        try {
            $displayInterval = $this->parser->normalize($displayString);
        } catch (\Exception $e) {
            $displayInterval = null;
        }

        if (!$displayInterval) {
            $text = "The display interval string \"{$displayString}\" cannot be parsed.";
            $this->context->buildViolation($text)
                ->atPath($constraint->path)
                ->addViolation();
        } else {
            if (!method_exists($object, $setter)) {
                $this->context->buildViolation("No setter function {$setter} found.")
                    ->atPath($contraint->path)
                    ->addViolation();
            }
            $object->$setter($displayInterval);
        }
    }
}
