<?php

namespace SniTodos\tests\Entity;

use SniTodos\Entity\Todo;
use SniTodos\Lib\Normalizer;

class Fixtures
{
    /**
     * @return Todo
     */
    public static function getTodo()
    {
        $todo = new Todo();
        $todo
            ->setDateString('24.12.')
            ->setDisplayTime('2d')
            ;

        return $todo;
    }

    public static function getNormalizedTodos()
    {
        $values = [
            ['2017-12-24', '2d', 'Xmas 17'],
            ['2017-12-31', '2d', 'New Year\'s Eve 17'],
            ['2018-12-24', '2d', 'Xmas 18'],
            ['2018-12-31', '2d', 'New Year\'s Eve 18'],
        ];

        $todos = [];
        foreach ($values as $value) {
            $key = 0;
            $todo = new Todo();
            $todo
                ->setDateString($value[$key++])
                ->setDisplayTime($value[$key++])
                ->setText($value[$key++]);

            Normalizer::getInstance()->normalize($todo);
            $todos[] = $todo;
        }

        return $todos;
    }
}
