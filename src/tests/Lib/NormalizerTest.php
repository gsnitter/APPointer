<?php

namespace SniTodos\tests\Lib;

use PHPUnit\Framework\TestCase;
use SniTodos\Lib\Normalizer;
use SniTodos\tests\Entity\Fixtures;
use SniTodos\Entity\Todo;

class NormalizerTest extends TestCase
{
    // @var Normalizer
    private $normalizer;

    // @var Todo
    private $todo;

    public function setUp()
    {
        $this->normalizer = Normalizer::getInstance();
        $this->todo = Fixtures::getTodo();
    }

    public function testGetParserClasses()
    {
        $this->normalizer->normalize($this->todo);
        $this->assertRegExp('@20\d{2}-12-24 23:59:59@', $this->todo->getNormalizedDateString());
        $this->assertSame('P0Y0M2DT0H0M0S', $this->todo->getNormalizedDisplayTime());
    }

    public function testNormalize()
    {
        $todo = new Todo();

        $todo->setDisplayTime('1d')->setText('egal')->setDateString('06.01.');
        $this->normalizer->normalize($todo);
        $this->assertRegExp('@\d{4}-01-06@', $todo->getNormalizedDateString());
    }
}
