<?php
declare(strict_types=1);

namespace SniTodos\Entity;

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
        // We split on commas, that are not preceded by a backslash.
        $parts = preg_split('@(?<!\\\);\s*@', $this->todoString);
        list($dateString, $text, $displayTime) = $parts;

        $return = [
            'dateString' => $dateString,
            'text' => $text,
            'displayTime' => $displayTime,
        ];

        if (isset($parts[3])) {
            $return['alarmTimes'] = preg_split('@\s*;\s*@', $parts[3]);
        }

        array_walk($return, function(&$value) {
            $value = str_replace('\\;', ';', $value);
        });

        return $return;
    }
}
