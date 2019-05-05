<?php

namespace APPointer\tests\Parser;

use PHPUnit\Framework\TestCase;
use APPointer\Parser\DisplayIntervalParser;

class DisplayIntervalParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new DisplayIntervalParser();
    }

    public function testDateInterval()
    {
        $time = new \DateTime('2000-01-01 00:00:00');
        $time->add(new \DateInterval('P1Y2M3DT5H6M7S'));

        $this->assertSame('2001-03-04 05:06:07', $time->format('Y-m-d H:i:s'));
    }

    public function testNormalizeYears()
    {
        $this->assertSame('2002-01-01 00:00:00', $this->getAdded('2j'));
        $this->assertSame('2002-01-01 00:00:00', $this->getAdded('2 Jahre'));
        $this->assertSame('2002-01-01 00:00:00', $this->getAdded('2 Years'));
        $this->assertSame('2002-01-01 00:00:00', $this->getAdded('2 y'));
    }

    public function testNormalizeMonths()
    {
        $this->assertSame('2000-03-01 00:00:00', $this->getAdded('2 m'));
        $this->assertSame('2000-03-01 00:00:00', $this->getAdded('2Months'));
        $this->assertSame('2000-03-01 00:00:00', $this->getAdded('2 Monate'));
    }

    public function testNormalizeDays()
    {
        $this->assertSame('2000-01-03 00:00:00', $this->getAdded('2d'));
        $this->assertSame('2000-01-03 00:00:00', $this->getAdded('2 D'));
        $this->assertSame('2000-01-03 00:00:00', $this->getAdded('2 Tage'));
    }

    public function testNormalizeHours()
    {
        $this->assertSame('2000-01-01 02:00:00', $this->getAdded('2h'));
        $this->assertSame('2000-01-01 02:00:00', $this->getAdded('2 Hours'));
        $this->assertSame('2000-01-01 02:00:00', $this->getAdded('2 Stunden'));
    }

    public function testNormalizeMinutes()
    {
        $this->assertSame('2000-01-01 00:30:00', $this->getAdded('30i'));
        $this->assertSame('2000-01-01 00:30:00', $this->getAdded('30 Minutes'));
        $this->assertSame('2000-01-01 00:30:00', $this->getAdded('30 Minuten'));
        $this->assertSame('2000-01-01 00:30:00', $this->getAdded('30 min'));
    }

    public function testNormalizeSeconds()
    {
        $this->assertSame('2000-01-01 00:01:30', $this->getAdded('90s'));
        $this->assertSame('2000-01-01 00:01:30', $this->getAdded('90 Seconds'));
        $this->assertSame('2000-01-01 00:01:30', $this->getAdded('90 Sekunden'));
        $this->assertSame('2000-01-01 00:01:30', $this->getAdded('90 sec'));
        $this->assertSame('2000-01-01 00:01:30', $this->getAdded('90 sek'));
    }

    public function getAdded($nonNormlizedString)
    {
        $dateInterval = $this->parser->normalize($nonNormlizedString);
        $time = new \DateTime('2000-01-01 00:00:00');

        return $time
            ->add($dateInterval)
            ->format('Y-m-d H:i:s');
    }

    public function test2d()
    {
        $dateInterval = $this->parser->normalize('2 d');
        $this->assertSame(2, $dateInterval->d);
    }
}
