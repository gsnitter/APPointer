<?php
namespace Sni\ExtendedOutputBundle\Entity;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class for a snippet of text. Knows the new offset withing the buffer's line.
 * Also knows its width in characters,
 *
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class TextSnippet
{
    private $text;
    private $newOffset;
    private $charCount;
    private $isEol;

    public function __construct(string $text, int $newOffset, int $charCount, bool $isEol)
    {
        $this->text = $text;
        $this->newOffset = $newOffset;
        $this->charCount = $charCount;
        $this->isEol = $isEol;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getNewOffset(): int
    {
        return $this->newOffset;
    }

    public function isEol()
    {
        return $this->isEol;
    }

    public function getCharCount(): int
    {
        return $this->charCount;
    }
}
