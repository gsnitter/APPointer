<?php

namespace APPointer\tests\Integration;

use PHPUnit\Framework\TestCase;
use APPointer\Entity\TodoString;
use APPointer\Entity\Todo;
use APPointer\Lib\Normalizer;
use APPointer\Lib\DI;

class AddTodoTest extends TestCase
{
    public function testAdd()
    {
        $todoString = '23:00; Zapfenstreich; 1 d; 22:50 grün/22:55/23:00 rot';

        $todoString = new TodoString($todoString);
        $todoArray = $todoString->toArray($todoString);
        $todo = Todo::createFromArray($todoArray);

        $errors = DI::getValidator()->validate($todo, null, ['Add']);

        $alarmTimes = $todo->getNormalizedAlarmTimes();
        $dateString = $this->getDateStringFor('23:00');
        $this->assertEquals(['time' => "{$dateString} 22:50", 'type' => 1], $alarmTimes[0]);
        $this->assertEquals(['time' => "{$dateString} 22:55", 'type' => 2], $alarmTimes[1]);
        $this->assertEquals(['time' => "{$dateString} 23:00", 'type' => 3], $alarmTimes[2]);
    }

    private function getDateStringFor(string $timeString): string
    {
        if (date('H:i') < $timeString) {
            return date('Y-m-d');
        } else {
            return (new \DateTime('+1 days'))->format('Y-m-d');
        }
    }
}
