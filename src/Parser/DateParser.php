<?php

namespace SniTodos\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * We want to be able to parse string like the followings
 * '31.12.', '23.08.2016 20:30'
 */
class DateParser {

    /**
     * @var \DateTie $now
     */
    private $now;

    /**
     * @param \DateTime $dt
     */
    public function setNow(\DateTime $dt): DateParser
    {
        $this->now = $dt;
        return $this;
    }

    /**
     * @param string
     * @return string
     * @throws \Exception
     */
    public function normalize(string $dateString): string
    {
        $dateString = $this->addTime($dateString);
        $dateString = $this->addYear($dateString);

        $dateString = (new \DateTime($dateString))->format('Y-m-d H:i:s');

        return $dateString;
    }

    public function getNow(): \DateTime
    {
        if (!$this->now) {
            $this->now = new \DateTime();
        }

        return $this->now;
    }

    /**
     * @var \DateTime $dt
     * @return string
     */
    public static function dateToString(\DateTime $dt): string
    {
        return $dt->format('Y-m-d H:i:s');
    }

    public function validate(string $dateString = null, ExecutionContextInterface $context, $payload)
    {
        if (!$dateString) {
            return;
        }

        try {
            $normalizedDateString = $this->normalize($dateString);
        } catch (\Exception $e) {
            $context->buildViolation("Unable to parse {$dateString}")
                ->atPath('dateString')
                ->addViolation();
        }
    }

    public function addTime(string $dateString): string
    {
        if (!preg_match('@(^|\D)\d{1,2}:\d{2}(:\d{2})?($|\D)@', $dateString)) {
            $dateString .= date(' 23:59:59');
        }

        return $dateString;
    }

    public function addYear(string $dateString): string
    {
        $originalString = $dateString;
        $dateString = preg_replace('@(\D|^)(\d{2}\.\d{2})\.(\D|$)@', '$1$2.' . date('Y') . '$3', $originalString);

        $dt = new \DateTime($dateString);

        if (self::dateToString($this->getNow()) > self::dateToString($dt)) {
            $dateString = preg_replace('@(\D|^)(\d{2}\.\d{2})\.(\D|$)@', '$1$2.' . (date('Y') + 1) . '$3', $originalString);
        }

        return $dateString;
    }
}
