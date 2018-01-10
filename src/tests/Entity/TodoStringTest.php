<?php

namespace SniTodos\tests\Entity;

use PHPUnit\Framework\TestCase;
use SniTodos\Entity\TodoString;

class TodoStringTest extends TestCase
{
    public function setUp()
    {
        $this->string = new TodoString(
            '31.12.; New year party\; same procedure as last year; 2d'
        );
        $this->result = $this->string->toArray();
    }

    public function testToArrayCorrectReturnType()
    {
        $this->assertTrue(is_array($this->result));
    }

    public function testToArrayKeys()
    {
        $keys = array_keys($this->result);
        sort($keys);
        $this->assertSame(['dateString', 'displayTime', 'text'], $keys);
    }

    public function testToArrayDateString()
    {
        $this->assertSame('31.12.', $this->result['dateString']);
    }

    public function testToArrayText()
    {
        $this->assertSame('New year party; same procedure as last year', $this->result['text']);
    }

    public function testToArrayDisplayTime()
    {
        $this->assertSame('2d', $this->result['displayTime']);
    }
}
