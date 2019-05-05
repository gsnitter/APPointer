<?php
namespace Sni\ExtendedOutputBundle\Tests\Service;

use Sni\ExtendedOutputBundle\Entity\TextSnippet;
use Sni\ExtendedOutputBundle\Service\LineCutter;
use PHPUnit\Framework\TestCase;

class LineCutterTest extends TestCase
{
    /** @var LineCutter */
    private $lineCutter;

    public function setUp()
    {
        $this->lineCutter = new LineCutter();
    }
   //
    public function testGetTextSnippetWithoutColors()
    {
        $line = 'Some text with umlaut ä but without any colors';

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 10);
        $this->assertSame('Some text ', $textSnippet->getText());
        $this->assertSame(10, $textSnippet->getCharCount());
        $this->assertSame(10, $textSnippet->getNewOffset());
        $this->assertSame(false, $textSnippet->isEol());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 5, 4);
        $this->assertSame('text', $textSnippet->getText());
        $this->assertSame(4, $textSnippet->getCharCount());
        $this->assertSame(9, $textSnippet->getNewOffset());
        $this->assertSame(false, $textSnippet->isEol());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 45);
        $this->assertSame('Some text with umlaut ä but without any color', $textSnippet->getText());
        $this->assertSame(45, $textSnippet->getCharCount());
        $this->assertSame(45, $textSnippet->getNewOffset());
        $this->assertSame(false, $textSnippet->isEol());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 46);
        $this->assertSame($line, $textSnippet->getText());
        $this->assertSame(46, $textSnippet->getCharCount());
        $this->assertSame(46, $textSnippet->getNewOffset());
        $this->assertSame(true, $textSnippet->isEol());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 47);
        $this->assertSame($line, $textSnippet->getText());
        $this->assertSame(46, $textSnippet->getCharCount());
        $this->assertSame(46, $textSnippet->getNewOffset());
        $this->assertSame(true, $textSnippet->isEol());

    }

    public function testGetTextSnippetWithColors()
    {
        $line = 'Some text with a single [32mgreen[39m word.';

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 23);
        $this->assertSame('Some text with a single', $textSnippet->getText());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 35);
        $this->assertSame($line, $textSnippet->getText());
    }

    public function testGetTextSnippetWithTwoColors()
    {
        $line = 'Some [43;1myellow[49;22m text with a [32mgreen[39m word.';

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 5);
        $this->assertSame('Some [43;1m', $textSnippet->getText());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 10);
        $this->assertSame('Some [43;1myello', $textSnippet->getText());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 12);
        $this->assertSame('Some [43;1myellow[49;22m ', $textSnippet->getText());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 27);
        $this->assertSame('Some [43;1myellow[49;22m text with a [32mgre', $textSnippet->getText());

        $textSnippet = $this->lineCutter->getTextSnippet($line, 0, 35);
        $this->assertSame('Some [43;1myellow[49;22m text with a [32mgreen[39m word.', $textSnippet->getText());
    }

    public function testGetTextSnippetRegression()
    {
        $line = "Some text with a single [32mgreen[39m word.";
        $textSnippet = $this->lineCutter->getTextSnippet($line, 20, 20);
        $this->assertRegExp('@^gle .*green.* word\.$@', $textSnippet->getText());
    }
}
