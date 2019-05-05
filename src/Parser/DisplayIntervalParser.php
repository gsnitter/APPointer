<?php

namespace APPointer\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DisplayIntervalParser extends ParserBase {

    private static $mappings = [
        'J' => 'Y', 'JAHRE' => 'Y', 'YEARS' => 'Y', 'Y' => 'Y',
        'M' => 'm', 'MONTHS' => 'm', 'MONATE' => 'm',
        'DAYS' => 'd', 'D' => 'd', 'DAY' => 'd', 'TAGE' => 'd', 'TAG' => 'd',
        'H' => 'H', 'HOURS' => 'H', 'STUNDEN' => 'H',
        'I' => 'i', 'MINUTES' => 'i', 'MINUTEN' => 'i', 'MIN' => 'i',
        'S' => 's', 'SECONDS' => 's', 'SEKUNDEN' => 's', 'SEC' => 's', 'SEK' => 's',
    ];


    /**
     * @param string
     * @return string
     * @throws \Exception
     */
    public function normalize($string): ?\DateInterval
    {
        try {
            $parts = $this->getParts($string);
            $this->parseIntegers($parts);
            $this->translateStrings($parts);

            return new \DateInterval($this->getDateIntervalString($parts));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function validate($string, ExecutionContextInterface $context, $payload)
    {
        if (!$string) {
            return;
        }

        try {
            $normalizedString = $this->normalize($string);
        } catch (\Exception $e) {
            $context->buildViolation("DisplayTimeParser: Unable to parse {$string}")
                ->atPath('displayTime')
                ->addViolation();
        }
    }

    /**
     * @param array $parts
     * return string - DateInterval String like 'P1Y2M3DT5H6M7S'
     */
    private function getDateIntervalString(array $parts): string
    {
        $result = array_fill_keys(['Y', 'm', 'd', 'H', 'i', 's'], 0);
        foreach ($parts as $key => $value) {
            if (is_numeric($value)) {
                continue;
            }

            if (isset($result[$value])) {
                $result[$value] = $parts[$key - 1];
            } else {
                throw new \Exception("Key $value is not a known time unit.");
            }
        }

        $dateIntervalString  = "P{$result['Y']}Y{$result['m']}M{$result['d']}D";
        $dateIntervalString .= "T{$result['H']}H{$result['i']}M{$result['s']}S";

        return $dateIntervalString;
    }

    /**
     * Returns an uppercased Array like ['2', 'D', '5', 'HOURS'] from '2d 5 hours'
     *
     * @param string $string
     * return array
     */
    private function getParts(string $string): array
    {
        $string = preg_replace('@\s+@', '', strtoupper($string));
        $parts  = preg_split('@(\d+)@', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts[0] == '') {
            array_shift($parts);
        }

        return $parts;
    }

    private function parseIntegers(array &$parts): DisplayIntervalParser
    {
        foreach ($parts as $key => &$value) {
            // Every even entry should be an integer 
            if ($key % 2 == 1) {
                continue;
            }

            if (!preg_match('@^\d+$@', $value)) {
                throw new \Exception("Expected $value to be an integer.");
            }

            $value = intval($value);
        }

        return $this;
    }

    private function translateStrings(array &$parts): DisplayIntervalParser
    {
        foreach ($parts as $key => &$value) {
            /**
             * Every odd entry should be a string, that we can map to our DateInterval-Entries.
             */
            if ($key % 2 == 0) {
                continue;
            }

            if (!isset(self::$mappings[$value])) {
                throw new \Exception("Cannot parse '$value' as a time unit.");
            }

            $value = self::$mappings[$value];
        }

        return $this;
    }
}
