<?php
declare(strict_types=1);

namespace APPointer\Entity;

/**
 * Splits expressions like
 * '23:00; Zapfenstreich; 1 d; 22:50 grün/22:55/23:00 rot'
 * in arrays with keys dateString, text, displayIntervalString an alarmTimes,
 * where the last again is an arra.
 */
class TodoString
{
    // @var string $todoString
    private $todoString;

    public function __construct(string $todoString)
    {
        $this->todoString = $todoString;
    }

    public function toArray(): array
    {
        // We split on semicolons, that are not preceded by a backslash.
        $parts = preg_split('@(?<!\\\);\s*@', $this->todoString);
        list($dateString, $text, $displayIntervalString) = $parts;

        $return = [
            'dateString' => $dateString,
            'text' => $text,
            'displayIntervalString' => $displayIntervalString,
        ];

        // We need at least 3 parts: Date, Name and some Date Interval telling
        // when we shall list the todo on app --show.
        if (count($parts) < 3) {
            $text  = 'Please provide at least 3 semicolon seperated parts.';
            $text .= ' See app --help.'; 
            throw new \InvalidArgumentException($text);
        }

        if (isset($parts[3])) {
            $return['alarmTimes'] = preg_split('@\s*/\s*@', $parts[3]);
        }

        array_walk($return, function(&$value) {
            $value = str_replace('\\;', ';', $value);
        });

        return $return;
    }
}
