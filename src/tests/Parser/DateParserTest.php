<?php

namespace APPointer\tests\Parser;

use PHPUnit\Framework\TestCase;
use APPointer\Parser\DateParser;

class DateParserTest extends TestCase
{

    private static $defaultTime = '23:59:59';

    public function setUp()
    {
        $this->parser = new DateParser();
        $this->parser->setNow(new \DateTime('24.12.2017 10:00'));
    }

    public function testNow()
    {
        $this->assertSame(date('Y-m-d'), (new DateParser())->getNow()->format('Y-m-d'));
        $this->assertSame('2017-12-24', $this->parser->getNow()->format('Y-m-d'));
    }

    private function getDateTime(string $dateString, string $timeString = null): \DateTime
    {
        $timeString = $timeString? : self::$defaultTime;
        return new \DateTime($dateString . ' ' . $timeString);
    }

    public function testNormalize()
    {
        $this->assertEquals($this->getDateTime('2017-12-23'), $this->parser->normalize('2017-12-23'));
        $this->assertEquals($this->getDateTime('2017-12-23'), $this->parser->normalize('23.12.2017'));
        $this->assertEquals($this->getDateTime('2018-12-23'), $this->parser->normalize('23.12.'));
        $this->assertEquals($this->getDateTime('2017-12-25'), $this->parser->normalize('25.12.'));
        $this->assertEquals($this->getDateTime('2017-12-24'), $this->parser->normalize('24.12.'));

        $this->assertEquals($this->getDateTime('2018-12-24', '09:00:00'), $this->parser->normalize('24.12. 09:00'));
        $this->assertEquals($this->getDateTime('2017-12-24', '11:00:00'), $this->parser->normalize('24.12. 11:00'));

        $this->assertEquals($this->getDateTime('2018-12-24', '09:00:00'), $this->parser->normalize('24.12. 9:00'));
        $this->assertEquals($this->getDateTime('2018-12-24', '09:00:00'), $this->parser->normalize('9:00 ,  24.12. '));

        $this->assertSame('12:00:00', $this->parser->normalize('morgen 12:00')->format('H:i:s'));
        $this->assertSame('12:00:00', $this->parser->normalize('in 3 Tagen 12:00')->format('H:i:s'));
        $this->assertSame('12:00:00', $this->parser->normalize('heute 12:00')->format('H:i:s'));
        $this->assertSame('12:00:00', $this->parser->normalize('am nächsten donnerstag 12:00')->format('H:i:s'));

        $this->assertSame('12:00:00', $this->parser->normalize('freitag 12:00')->format('H:i:s'));
        $this->assertSame('12:00:00', $this->parser->normalize('nächster freitag 12:00')->format('H:i:s'));
    }

    public function testNormalizeHeiligeDreiKoenige()
    {
        $this->assertEquals($this->getDateTime('2018-01-06'), $this->parser->normalize('06.01.'));
    }

    public function testTimeTodayCorrectOrder()
    {
        $this->assertEquals(new \DateTime('today 23:00:00'), $this->parser->normalize('heute 23:00'));
    }

    public function testTimeTodayWrongOrder()
    {
        $this->assertEquals(new \DateTime('today 23:00:00'), $this->parser->normalize('23:00 heute'));
    }
}
