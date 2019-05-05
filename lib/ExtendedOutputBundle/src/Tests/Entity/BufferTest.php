<?php
namespace Sni\ExtendedOutputBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Sni\ExtendedOutputBundle\Entity\Buffer;
use Symfony\Component\Console\Helper\Table;

class BufferTest extends TestCase
{

    public function testGetLine()
    {
        $buffer = new Buffer();
        $buffer->getOutput()->writeln('Some text with a single <info>green</info> word.');
        $table = new Table($buffer->getOutput());
        $table->setHeaders(['One', 'Two'])
            ->setRows([[1, 2]]);
        $table->render();
        $buffer->getOutput()->writeln("<bg=yellow;options=bold>Some bold text with yellow background\nwith two lines.</>");

        $this->assertSame(null, $buffer->getLine(-1));
        $this->assertSame('Some text with a single [32mgreen[39m word.', $buffer->getLine(0));
        $line = $buffer->getLine(1);
        $this->assertSame('+-----+-----+', $buffer->getLine(1));
$this->assertSame('|[32m One [39m|[32m Two [39m|', $buffer->getLine(2));
        $this->assertSame('+-----+-----+', $buffer->getLine(3));
        $this->assertSame('| 1   | 2   |', $buffer->getLine(4));
        $this->assertSame('+-----+-----+', $buffer->getLine(5));
        $this->assertSame('[43;1mSome bold text with yellow background', $buffer->getLine(6));

        $this->assertSame('with two lines.[49;22m', $buffer->getLine(7));
        $this->assertSame('', $buffer->getLine(8));
        $this->assertSame(null, $buffer->getLine(9));
    }
}
