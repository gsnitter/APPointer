<?php

namespace APPointer\tests\Entity;

use PHPUnit\Framework\TestCase;
use APPointer\Entity\DzenMessage;

class DzenMessageTest extends TestCase
{
    public function testStringToType()
    {
        $this->assertSame(DzenMessage::stringToType('grün'), DzenMessage::GOOD_NEWS);
        $this->assertSame(DzenMessage::stringToType('green'), DzenMessage::GOOD_NEWS);
        $this->assertSame(DzenMessage::stringToType('good'), DzenMessage::GOOD_NEWS);
        $this->assertSame(DzenMessage::stringToType('normal'), DzenMessage::NORMAL);
        $this->assertSame(DzenMessage::stringToType('red'), DzenMessage::BAD_NEWS);

        $this->assertSame(DzenMessage::stringToType('irgendwas'), 0);
    }
}
