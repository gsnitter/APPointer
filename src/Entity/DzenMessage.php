<?php
declare(strict_types=1);

namespace APPointer\Entity;

/**
 * TODO SNI: This is a relict, functionality should be implemented in AlarmTime.
 */
class DzenMessage
{
    const GOOD_NEWS = 1;
    const NORMAL = 2;
    const BAD_NEWS = 3;

    public static function stringToType(string $string): int
    {
        $string = trim(strtolower($string));

        if (in_array($string, ['good', 'green', 'grün'])) {
            return self::GOOD_NEWS;
        }
        if (in_array($string, ['normal', 'blue', 'blau'])) {
            return self::NORMAL;
        }
        if (in_array($string, ['bad', 'red', 'rot'])) {
            return self::BAD_NEWS;
        }

        return 0;
    }
}
