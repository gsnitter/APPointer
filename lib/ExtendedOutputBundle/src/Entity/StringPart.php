<?php
namespace Sni\ExtendedOutputBundle\Entity;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class for a part of text. Remembers its size in characters
 *
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class StringPart
{
    private $string;
    private $size;

    public function __construct(string $string)
    {
        $this->string = $string;
        $this->size = mb_strlen($string);
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWidth(): int
    {
        return $this->getSize();
    }

    public function strcutLeft(int $width): string
    {
        return mb_strcut($this->string, 0, $width);
    }

    public function getSubstring($offset, $width): string
    {
        if ($offset > $this->size || $width <= 0) {
            return '';
        }
        return mb_substr($this->string, $offset, $width);
    }
}
