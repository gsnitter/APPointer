<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
// TODO SNI
use APPointer\Lib\Filesystem;
use APPointer\Parser\DisplayTimeParser;

class DisplayTimeNormalizerValidator extends ConstraintValidator
{
    // TODO SNI
    public function __construct(DisplayTimeParser $parser)
    {
        $this->parser = $parser;
    }

    public function validate($object, Constraint $constraint)
    {
        // $path = $this->context->getPropertyPath();

        // $text = "Unable to parse date string \"{$value}\" in property \"{$path}\".";

        $getter = 'get' . ucfirst($constraint->path);
        $setter = 'setNormalized' . ucfirst($constraint->path);

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
            // TODO SNI
            $normalizedDisplayTime = $this->parser->normalize($displayString);
        } catch (\Exception $e) {
            $normalizedDisplayTime = '';
        }

        if (!$normalizedDisplayTime) {
            $text = "The display time string \"{$displayString}\" cannot be parsed.";
            $this->context->buildViolation($text)
                ->atPath($constraint->path)
                ->addViolation();
        } else {
            if (!method_exists($object, $setter)) {
                $this->context->buildViolation("No setter function {$setter} found.")
                    ->atPath($contraint->path)
                    ->addViolation();
            }
            $object->$setter($normalizedDisplayTime);
        }
    }
}
