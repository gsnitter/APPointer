<?php

namespace SniTodos\tests\Lib;

use PHPUnit\Framework\TestCase;
use SniTodos\Entity\Todo;
use SniTodos\Lib\TodosFileParser;
use SniTodos\Lib\DI;

class TodosFileParserTest extends TestCase
{
    /** @var TodosFileParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new TodosFileParser();

        $file = $this->getMockBuilder('SniTodos\Entity\GoogleFileProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->any())
            ->method('parseYaml')
            ->willReturn([[
                'dateString' => '09.01.2018',
                'normalizedDateString' => '2018-01-09 23:59:59',
                'displayTime' => '',
                'normalizedDisplayTime' => 'P0Y0M0DT0H0M0S',
                'alarmTimes' => ['22:00 red'],
                'normalizedAlarmTimes' => [['time' =>  '2018-01-09 22:00', 'type' => 3]],
                'text' => 'Go to bed',
            ],
            [
                'dateString' => '09.01.2018 22:00',
                'normalizedDateString' => '2017-01-09 22:00:00',
                'displayTime' => '2d',
                'normalizedDisplayTime' => 'P0Y0M7DT0H0M0S',
                'text' => 'Check TodosFileParser',
                'alarmTimes' => [['time' => '2018-01-09 20:45', 'type' => 1], ['time' => '2018-01-09 20:47', 'type' =>  3]],
                'normalizedAlarmTimes' => [['time' => '2018-01-09 20:45', 'type' => 1], ['time' => '2018-01-09 20:47', 'type' => 3]],
            ]]);
        $this->parser->setGoogleFile($file);
    }

    public function testGetTodos()
    {
        $todos = $this->parser->getTodos();
        $this->assertSame(2, count($todos));
        $first = $todos[0];
        $second = $todos[1];
        $this->assertInstanceOf(Todo::class, $first);
        $this->assertInstanceOf(Todo::class, $second);

        $this->assertSame(
            [['time' => '2018-01-09 20:45', 'type' => 1], ['time' => '2018-01-09 20:47', 'type' => 3]],
            $second->getNormalizedAlarmTimes()
        );
    }

    public function testGetAlarmTimes()
    {
        $alarmTimes = $this->parser->getAlarmTimes();
        $this->assertEquals(
            [
                ['time' => '2018-01-09 22:00', 'type' => 3, 'message' => 'Go to bed'],
                ['time' => '2018-01-09 20:45', 'type' => 1, 'message' => 'Check TodosFileParser'],
                ['time' => '2018-01-09 20:47', 'type' => 3, 'message' => 'Check TodosFileParser']
            ],
            $alarmTimes
        );
    }
}
