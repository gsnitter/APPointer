<?php declare(strict_types = 1);

namespace APPointer\tests\Parser;

use APPointer\Entity\DzenMessage;
use APPointer\Parser\AlarmTimesParser;
use PHPUnit\Framework\TestCase;

class AlarmTimesParserTest extends TestCase
{
    public function setUp(): void
    {
        $this->parser = new AlarmTimesParser();
        $this->parser->setDate(new \DateTime('2017-01-14 22:00:00'));
    }

    public function testNeedsNormalizedValues(): void
    {
        $this->assertSame(['dateString'], $this->parser->getNeededNormalizedValues());
    }

    public function testNormalizeFullArrayNotation(): void
    {
        $value = [['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]];
        $this->assertSame([
            ['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithoutDate(): void
    {
        $value = [['time' => '20:00', 'type' => DzenMessage::GOOD_NEWS]];
        $this->assertSame([
            ['time' => '2017-01-14 20:00', 'type' => DzenMessage::GOOD_NEWS]
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithoutType(): void
    {
        $value = [['time' => '2017-01-13 20:00']];
        $this->assertSame([
            ['time' => '2017-01-13 20:00', 'type' => DzenMessage::NORMAL]
        ], $this->parser->normalize($value));

        // By the way: We did not test yet, that the original array does not change
        $this->assertSame([['time' => '2017-01-13 20:00']], $value);
    }

    public function testNormalizeArrayWithStrings(): void
    {
        $value = ['2017-01-13 22:00 green', '2017-01-13 22:05 normal',  '2017-01-13 22:10',  '2017-01-13 22:15 red'];
        $this->assertSame([
            ['time' => '2017-01-13 22:00', 'type' => DzenMessage::GOOD_NEWS],
            ['time' => '2017-01-13 22:05', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-13 22:10', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-13 22:15', 'type' => DzenMessage::BAD_NEWS],
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithStringsNoDates(): void
    {
        $value = ['22:00 grün', '22:05',  '22:15 bad'];
        $this->assertSame([
            ['time' => '2017-01-14 22:00', 'type' => DzenMessage::GOOD_NEWS],
            ['time' => '2017-01-14 22:05', 'type' => DzenMessage::NORMAL],
            ['time' => '2017-01-14 22:15', 'type' => DzenMessage::BAD_NEWS],
        ], $this->parser->normalize($value));
    }

    public function testNormalizeArrayWithStringsNoTime(): void
    {
        $value = ['2017-01-14 green'];
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('No parseable time string');
        $this->expectExceptionMessage("'2017-01-14 green'");
        $this->parser->normalize($value);
    }

    public function testNormalizeSingleString(): void
    {
        $value = '2017-01-14 10:00 rot';
        $result = $this->parser->normalize($value);

        $this->assertEquals([[
            'time' => '2017-01-14 10:00',
            'type' => DzenMessage::BAD_NEWS,
        ]], $result);
    }

    public function testNormalizeOneDigitTimeString(): void
    {
        $value = '9:00';
        $result = $this->parser->normalize($value);

        $this->assertEquals([[
            'time' => '2017-01-14 9:00',
            'type' => DzenMessage::NORMAL,
        ]], $result);
    }
}
