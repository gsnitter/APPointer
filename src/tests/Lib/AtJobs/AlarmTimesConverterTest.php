<?php
namespace APPointer\tests\Lib\AtJobs;

use PHPUnit\Framework\TestCase;
use APPointer\Lib\AtJobs\AlarmTimesConverter;
use APPointer\Entity\DzenMessage;

class AlarmTimesConverterTest extends TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new AlarmTimesConverter();
    }

    public function testCreateDzenMessage()
    {
        $alarmTimeArray = ['time' =>  '2018-01-09 22:00', 'type' => 1, 'message' => 'eins'];
        $message = $this->converter->createDzenMessage($alarmTimeArray);

        $this->assertInstanceOf(DzenMessage::class, $message);
        $this->assertSame('eins', $message->getMessage());
        $this->assertSame('1801092200', $message->getNormalizedAtTimeString());
        $this->assertSame('1801092200', $message->getNormalizedAtTimeString());
        $this->assertSame(1, $message->getType());
    }

    public function testCreateDzenMessages()
    {
        $alarmTimesArray = [
            ['time' =>  '2018-01-09 22:00', 'type' => 1, 'message' => 'eins'],
            ['time' =>  '2018-01-09 22:05', 'type' => 2, 'message' => 'zwei'],
            ['time' =>  '2018-01-09 22:10', 'type' => 3, 'message' => 'drei'],
        ];
        $messages = $this->converter->createDzenMessages($alarmTimesArray);

        $message = $messages[0];
        $this->assertInstanceOf(DzenMessage::class, $message);
        $this->assertSame('eins', $message->getMessage());
        $this->assertSame('1801092200', $message->getNormalizedAtTimeString());

        $message = $messages[1];
        $this->assertSame('zwei', $message->getMessage());
        $this->assertSame(2, $message->getType());
        $this->assertSame('1801092205', $message->getNormalizedAtTimeString());

        $message = $messages[2];
        $this->assertSame('drei', $message->getMessage());
        $this->assertSame(3, $message->getType());
        $this->assertSame('1801092210', $message->getNormalizedAtTimeString());
    }
}
