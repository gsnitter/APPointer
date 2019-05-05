<?php
namespace Sni\ExtendedOutputBundle\Service;

use Sni\ExtendedOutputBundle\Entity\Buffer;
use Sni\ExtendedOutputBundle\Entity\Viewport;

interface TextProjectorInterface
{
    public function getLines(Buffer $buffer, Viewport $viewport, int $lineNumber, int $charOffset): array;
}
