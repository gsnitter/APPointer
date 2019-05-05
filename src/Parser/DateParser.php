<?php

namespace APPointer\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * We want to be able to parse string like the followings
 * '31.12.', '23.08.2016 20:30', 'morgen 3:00 Uhr', 'in 3 Tagen um 12:00'
 */
class DateParser extends ParserBase {

    /**
     * @var \$now
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
    public function normalize($dateString): ?\DateTime
    {
        try {
            $dateString = $this->translateWords($dateString);
            $dateString = $this->addTime($dateString);
            $dateString = $this->addYear($dateString);

            return new \DateTime($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function translateWords(string $dateString): string
    {
        $dateString = strtolower($dateString);
        $dateString = str_replace('heute', 'today', $dateString);
        $dateString = str_replace('morgen', 'tomorrow', $dateString);
        $dateString = str_replace('in', '', $dateString);
        $dateString = str_replace('um', '', $dateString);
        $dateString = str_replace('tagen', 'days', $dateString);
        $dateString = str_replace('am ', '', $dateString);
        $dateString = str_replace('nächsten', '', $dateString);
        $dateString = str_replace('diesen', '', $dateString);
        $dateString = str_replace('nächster', '', $dateString);

        $weekDays = [
            'montag' => 'monday',
            'dienstag' => 'tuesday',
            'mittwoch' => 'wednesday',
            'donnerstag' => 'thursday',
            'freitag' => 'friday',
            'samstag' => 'saturday',
            'sonntag' => 'sunday',
        ];

        foreach ($weekDays as $original => $translation) {
            $dateString = str_replace($original, $translation, $dateString);
        }

        // \DateTime('today 13:00') works, but not ('13:00 today')
        $dateString = preg_replace('@^(.*)\s+(\w+)$@', '\2 \1', $dateString);
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

    public function validate($dateString = null, ExecutionContextInterface $context, $payload)
    {
        // TODO SNI
        var_dump($this->fs);
        if (!$dateString) {
            return;
        }

        try {
            $normalizedDateString = $this->normalize($dateString);
        } catch (\Exception $e) {
            $context->buildViolation("DateParser: Unable to parse {$dateString}")
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
        $thisYear = intval($this->getNow()->format('Y'));
        $originalString = $dateString;
        $dateString = preg_replace('@(\D|^)(\d{2}\.\d{2})\.(\D|$)@', '$1$2.' . $thisYear, $originalString);

        $dt = new \DateTime($dateString);

        if (self::dateToString($this->getNow()) > self::dateToString($dt)) {
            $dateString = preg_replace('@(\D|^)(\d{2}\.\d{2})\.(\D|$)@', '$1$2.' . ($thisYear + 1) . '$3', $originalString);
        }

        return $dateString;
    }
}
