<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
// TODO SNI
use APPointer\Lib\Filesystem;
use APPointer\Parser\DateParser;

class DateStringNormalizerValidator extends ConstraintValidator
{
    // TODO SNI
    public function __construct(DateParser $parser)
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

        $dateString = $object->$getter();
        if (!$dateString) {
            return;
        }

        try {
            $normalizedDateString = $this->parser->normalize($dateString);
        } catch (\Exception $e) {
            $normalizedDateString = '';
        }

        if (!$normalizedDateString) {
            $text = "The date string \"{$dateString}\" cannot be parsed.";
            $this->context->buildViolation($text)
                ->atPath($constraint->path)
                ->addViolation();
        } else {
            if (!method_exists($object, $setter)) {
                $this->context->buildViolation("No setter function {$setter} found.")
                    ->atPath($contraint->path)
                    ->addViolation();
            }
            $object->$setter($normalizedDateString);
        }
    }
}
