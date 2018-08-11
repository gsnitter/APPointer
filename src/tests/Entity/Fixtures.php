<?php

namespace APPointer\tests\Entity;

use APPointer\Entity\Todo;
use APPointer\Lib\Normalizer;

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

    public static function getForeignTodoFileContent()
    {
        return <<<EOT
-
    normalizedCreatedAt: '2018-03-01 10:00:00'
    normalizedUpdatedAt: '2018-03-01 10:00:00'
    dateString: '24.03.2018 17:00'
    normalizedDateString: '2018-03-24 17:00:00'
    displayTime: 2d
    normalizedDisplayTime: P0Y0M2DT0H0M0S
    alarmTimes: null
    normalizedAlarmTimes: {  }
    text: 'Erstes Todo'
-
    normalizedCreatedAt: '2018-03-01 12:00:00'
    normalizedUpdatedAt: '2018-03-01 14:00:00'
    dateString: '24.04.2018 17:00'
    normalizedDateString: '2018-04-24 17:00:00'
    displayTime: 2d
    normalizedDisplayTime: P0Y0M2DT0H0M0S
    alarmTimes: null
    normalizedAlarmTimes: [{ time: '2018-04-07 15:30', type: 1 }]
    text: 'Zweites Todo'

EOT;
    }

    public static function getLocalTodoFileContent()
    {
        return <<<EOT
-
    normalizedCreatedAt: '2018-03-01 14:00:00'
    normalizedUpdatedAt: '2018-03-01 14:00:00'
    dateString: '24.05.2018 17:00'
    normalizedDateString: '2018-05-24 17:00:00'
    displayTime: 2d
    normalizedDisplayTime: P0Y0M2DT0H0M0S
    alarmTimes: null
    normalizedAlarmTimes: {  }
    text: 'Erstes anderes Todo'
-
    normalizedCreatedAt: '2018-03-01 12:00:00'
    normalizedUpdatedAt: '2018-03-01 12:00:00'
    dateString: '24.04.2018 18:00'
    normalizedDateString: '2018-04-24 18:00:00'
    displayTime: 2d
    normalizedDisplayTime: P0Y0M2DT0H0M0S
    alarmTimes: null
    normalizedAlarmTimes: [{ time: '2018-04-07 15:35', type: 1 }]
    text: 'Zweites anderes Todo'

EOT;
    }
}
