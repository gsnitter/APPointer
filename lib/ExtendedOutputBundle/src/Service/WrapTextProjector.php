<?php
namespace Sni\ExtendedOutputBundle\Service;

use Sni\ExtendedOutputBundle\Entity\Buffer;
use Sni\ExtendedOutputBundle\Entity\Viewport;

class WrapTextProjector implements TextProjectorInterface
{
    private $lineCutter;

    public function __construct(LineCutter $lineCutter)
    {
        $this->lineCutter = $lineCutter;
    }

    public function getLines(Buffer $buffer, Viewport $viewport, int $lineNumber, int $charOffset): array
    {
        $result = [];

        while ($buffer->getLine($lineNumber) && count($result) <= $viewport->getHeight()) {
            $line = $buffer->getLine($lineNumber);

            if ($line === null) {
                break;
            }

            $debugCalledBefore = count($result);
            $textSnippet = $this->lineCutter->getTextSnippet($line, $charOffset, $viewport->getWidth(), $debugCalledBefore);
            $result[] = $textSnippet->getText() . str_repeat(' ', $viewport->getWidth() - $textSnippet->getCharCount());

            if ($textSnippet->isEol()) {
                $lineNumber++;
                $charOffset = 0;
            } else {
                $charOffset += $viewport->getWidth();
            }
        }

        while (count($result) < $viewport->getHeight()) {
            $result[] = str_repeat(' ', $viewport->getWidth());
        }

        return array_slice($result, 0, $viewport->getHeight());
    }
}
