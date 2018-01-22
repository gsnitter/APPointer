<?php
declare(strict_types=1);

namespace SniTodos\Entity;

/**
 * Splits expressions like
 * '23:00; Zapfenstreich; 1 d; 22:50 grün/22:55/23:00 rot'
 * in arrays with keys dateString, text, displayTime an alarmTimes,
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
        list($dateString, $text, $displayTime) = $parts;

        $return = [
            'dateString' => $dateString,
            'text' => $text,
            'displayTime' => $displayTime,
        ];

        if (isset($parts[3])) {
            $return['alarmTimes'] = preg_split('@\s*/\s*@', $parts[3]);
        }

        array_walk($return, function(&$value) {
            $value = str_replace('\\;', ';', $value);
        });

        return $return;
    }
}
