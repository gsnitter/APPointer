<?php
namespace Sni\ExtendedOutputBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sni\ExtendedOutputBundle\Entity\Buffer;
use Symfony\Component\Console\Helper\Table;
use Sni\ExtendedOutputBundle\Service\WrapTextProjector;
use Sni\ExtendedOutputBundle\Entity\Viewport;
use Sni\ExtendedOutputBundle\Entity\TextSnippet;

class WrapTextProjectorTest extends TestCase
{
    private $viewportLineNumber = 0;

    public function setUp()
    {
        $this->buffer = new Buffer();
        $this->buffer->getOutput()->writeln('Some text with a single <info>green</info> word.');
        $table = new Table($this->buffer->getOutput());
        $table->setHeaders(['One', 'Two'])
            ->setRows([[1, 2]]);
        $table->render();
        $this->buffer->getOutput()->writeln("<bg=yellow;options=bold>Some bold text with yellow background\nwith two lines.</>");

        $stream = $this->buffer->getOutput()->getStream();
        rewind($stream);
        $content = stream_get_contents($stream);

        $lineCutter = $this->getMockBuilder('Sni\ExtendedOutputBundle\Service\LineCutter')
            ->setMethods(['getTextSnippet'])
            ->getMock();
        $lineCutter->expects($this->spy = $this->any())
            ->method('getTextSnippet')
            ->will($this->returnCallback([$this, 'getTextSnippet']));

        $this->projector = new WrapTextProjector($lineCutter);
    }

    public function testGetLinesLongViewport()
    {
        $viewport = new Viewport(5, 5, 20, 9);
        $lines = $this->projector->getLines($this->buffer, $viewport, 0, 0);

        $invos = $this->spy->getInvocations();
        $allParams = [];
        foreach ($invos as $invo) {
            $allParams[] = $invo->getParameters();
        }

        $lineNumber = 0;
        $params = $allParams[$lineNumber];
        $this->assertSame('Some text with a single [32mgreen[39m word.', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('Some text with a sin', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('Some text with a single [32mgreen[39m word.', $params[0]);
        $this->assertSame(20, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertRegExp('@^gle .*green.* word.     $@', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('+-----+-----+', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('+-----+-----+       ', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('|[32m One [39m|[32m Two [39m|', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('|[32m One [39m|[32m Two [39m|       ', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('+-----+-----+', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('+-----+-----+       ', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('| 1   | 2   |', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('| 1   | 2   |       ', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('+-----+-----+', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('+-----+-----+       ', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame("[43;1mSome bold text with yellow background", $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame("[43;1mSome bold text with yellow background", $params[0]);
        $this->assertSame(20, $params[1]);
        $this->assertSame(20, $params[2]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        // TODO SNI: Alter Farbcode?
        $this->assertSame('with two lines.[49;22m', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
   }

    public function testGetLinesShortViewport()
    {
        $viewport = new Viewport(5, 5, 20, 3);
        $lines = $this->projector->getLines($this->buffer, $viewport, 0, 0);

        $invos = $this->spy->getInvocations();
        $allParams = [];
        foreach ($invos as $invo) {
            $allParams[] = $invo->getParameters();
        }

        $lineNumber = 0;
        $params = $allParams[$lineNumber];
        $this->assertSame('Some text with a single [32mgreen[39m word.', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('Some text with a sin', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('Some text with a single [32mgreen[39m word.', $params[0]);
        $this->assertSame(20, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertRegExp('@^gle .*green.* word.     $@', $lines[$lineNumber]);

        $lineNumber++;
        $params = $allParams[$lineNumber];
        $this->assertSame('+-----+-----+', $params[0]);
        $this->assertSame(0, $params[1]);
        $this->assertSame(20, $params[2]);
        $this->assertSame('+-----+-----+       ', $lines[$lineNumber]);

        $this->assertCount(3, $lines);
   }

    public function getTextSnippet($line, int $charOffset, int $width): TextSnippet
    {
        switch ($this->viewportLineNumber) {
            case 0:
                // $text, $offset, $charCount, $eol
                $textSnippet = new TextSnippet('Some text with a sin', 0, 20, false);
                break;
            case 1:
                $textSnippet = new TextSnippet('gle [some color code]green[some other color code] word.', 20, 15, true);
                break;
            case 2:
                $textSnippet = new TextSnippet('+-----+-----+', 0, 13, true);
                break;
            case 3:
                $textSnippet = new TextSnippet('|[32m One [39m|[32m Two [39m|', 0, 13, true);
                break;
            case 4:
                $textSnippet = new TextSnippet('+-----+-----+', 0, 13, true);
                break;
            case 5:
                $textSnippet = new TextSnippet('| 1   | 2   |', 0, 13, true);
                break;
            case 6:
                $textSnippet = new TextSnippet('+-----+-----+', 0, 13, true);
                break;
            case 7:
                $textSnippet = new TextSnippet('[43;1mSome bold text with ', 0, 20, false);
                break;
            case 8:
                $textSnippet = new TextSnippet('yellow background', 20, 17, true);
                break;
            case 9:
                $textSnippet = new TextSnippet('with two lines.[49;22m', 0, 15, true);
                break;
            default:
                $textSnippet = new TextSnippet('', 126, 0, true);
                break;
        }
        $this->viewportLineNumber++;
        return $textSnippet;
    }
}
