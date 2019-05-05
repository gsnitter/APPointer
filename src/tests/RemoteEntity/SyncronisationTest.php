<?php

namespace APPointer\tests\RemoteEntity;

use PHPUnit\Framework\TestCase;
use APPointer\RemoteEntity\Syncronisation;

class SyncronisationTest extends TestCase
{
    public function testCreateWithFromTo(): void
    {
        $s = Syncronisation::createWithSourceTarget('source host name', 'target host name');
        $this->assertInstanceOf(Syncronisation::class, $s);

        $this->assertSame('source host name', $s->getSource());
        $this->assertSame('target host name', $s->getTarget());
        $this->assertSame(date('d.m.Y H:i'), $s->getTime()->format('d.m.Y H:i'));
    }
}
