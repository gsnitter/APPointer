<?php

namespace APPointer\tests\Entity;

use PHPUnit\Framework\TestCase;
use APPointer\Entity\DzenMessage;

class DzenMessageTest extends TestCase
{
    public function testStringToType()
    {
        $m = new DzenMessage('Test', '2030-12-31 23:59');
        $m->setType(DzenMessage::GOOD_NEWS);
        $command = $m->getInstallCommand();

        $this->assertRegExp('@^echo "export DISPLAY=\$DISPLAY;"@', $command);
        $this->assertRegExp("@dzen2 -p -x '\d{3}' -y '\d{2}'@", $command);
        $this->assertContains("-bg darkgreen -fg green", $command);
        $this->assertContains("at -t '3012312359'", $command);
    }
}
