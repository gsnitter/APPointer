<?php

namespace SniTodos\tests\Entity;

use SniTodos\Entity\Todo;

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
}
