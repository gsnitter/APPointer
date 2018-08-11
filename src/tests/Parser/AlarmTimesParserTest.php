<?php

namespace APPointer\tests\Parser;

use PHPUnit\Framework\TestCase;
use APPointer\Parser\AlarmTimesParser;
use APPointer\Entity\DzenMessage;

class AlarmTimesParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new AlarmTimesParser();
        $this->parser->setNormalizedDateString('2017-01-14 22:00:00');
    }

    public function testNeedsNormalizedValues()
    {
        $this->assertSame(['dateString'], $this->parser->getNeededNormalizedValues());
    }

    public function testNormalizeFullArrayNotation()
    {
        $value = [['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]];
        $this->assertSame([
            ['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithoutDate()
    {
        $value = [['time' => '20:00', 'type' => DzenMessage::GOOD_NEWS]];
        $this->assertSame([
            ['time' => '2017-01-14 20:00', 'type' => DzenMessage::GOOD_NEWS]
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithoutType()
    {
        $value = [['time' => '2017-01-13 20:00']];
        $this->assertSame([
            ['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]
        ], $this->parser->normalize($value));

        // By the way: We did not test yet, that the original array does not change
        $this->assertSame([['time' => '2017-01-13 20:00']], $value);
    }

    public function testNormalizeArrayWithStrings()
    {
        $value = ['2017-01-13 22:00 green', '2017-01-13 22:05 normal',  '2017-01-13 22:10',  '2017-01-13 22:15 red'];
        $this->assertSame([
            ['time' => '2017-01-13 22:00', 'type' => DzenMessage::GOOD_NEWS],
            ['time' => '2017-01-13 22:05', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-13 22:10', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-13 22:15', 'type' => DzenMessage::BAD_NEWS],
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithStringsNoDates()
    {
        $value = ['22:00 grün', '22:05',  '22:15 bad'];
        $this->assertSame([
            ['time' => '2017-01-14 22:00', 'type' => DzenMessage::GOOD_NEWS],
            ['time' => '2017-01-14 22:05', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-14 22:15', 'type' => DzenMessage::BAD_NEWS],
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithStringsNoTime()
    {
        $value = ['2017-01-14 green'];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No parseable time string');
        $this->expectExceptionMessage("'2017-01-14 green'");
        $this->parser->normalize($value);
    }

    public function testNormalizeSingleString()
    {
        $value = '2017-01-14 10:00 rot';
        $result = $this->parser->normalize($value);

        $this->assertEquals([[
            'time' => '2017-01-14 10:00',
            'type' => DzenMessage::BAD_NEWS,
        ]], $result);
    }
}
