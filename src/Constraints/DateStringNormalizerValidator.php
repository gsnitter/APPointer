<?php

namespace APPointer\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use APPointer\Parser\DateParser;

class DateStringNormalizerValidator extends ConstraintValidator
{
    public function __construct(DateParser $parser)
    {
        $this->parser = $parser;
    }

    public function validate($object, Constraint $constraint)
    {
        $getter = 'get' . ucfirst($constraint->path);
        $setter = 'set' . str_replace('String', '', ucfirst($constraint->path));

        if ($getter === preg_replace('/^s/', 'g', $setter)) {
            throw new \Exception("Property name {$constraint->path} has to contain 'String'.");
        }

        if (!method_exists($object, $getter)) {
            $this->context->buildViolation("No getter function {$getter} found.")
                ->atPath($constraint->path)
                ->addViolation();
        }

        $dateString = $object->$getter();
        if (!$dateString) {
            return;
        }

        $this->handleCronString($dateString, $setter, $object, $constraint);
        $dateString = $object->$getter();

        $date = $this->parser->normalize($dateString);

        if (!$date) {
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
            $object->$setter($date);
        }
    }

    public function handleCronString(string $dateString, string $setter, $object, Constraint $constraint): bool
    {
        try {
            $cron = \Cron\CronExpression::factory($dateString);

            $setter = $setter . 'String';
            $object->$setter($cron->getNextRunDate()->format('Y-m-d H:i:s'));
            $object->setCronExpression($dateString);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
