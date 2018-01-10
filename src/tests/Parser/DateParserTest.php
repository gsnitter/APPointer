<?php

namespace SniTodos\tests\Parser;

use PHPUnit\Framework\TestCase;
use SniTodos\Parser\DateParser;

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

    public function testNormalize()
    {
        $this->assertSame('2017-12-23 ' . self::$defaultTime, $this->parser->normalize('23.12.2017'));
        $this->assertSame('2018-12-23 ' . self::$defaultTime, $this->parser->normalize('23.12.'));
        $this->assertSame('2017-12-25 ' . self::$defaultTime, $this->parser->normalize('25.12.'));
        $this->assertSame('2017-12-24 ' . self::$defaultTime, $this->parser->normalize('24.12.'));

        $this->assertSame('2018-12-24 09:00:00', $this->parser->normalize('24.12. 09:00'));
        $this->assertSame('2017-12-24 11:00:00', $this->parser->normalize('24.12. 11:00'));

        $this->assertSame('2018-12-24 09:00:00', $this->parser->normalize('24.12. 9:00'));
        $this->assertSame('2018-12-24 09:00:00', $this->parser->normalize('9:00 24.12. '));
    }

    public function testNormalizeHeiligeDreiKoenige()
    {
        $this->assertSame('2018-01-06 23:59:59', $this->parser->normalize('06.01.'));
    }

    public function testNeedsNormalizedValues()
    {
        $this->assertSame([], $this->parser->getNeededNormalizedValues());
    }
}
