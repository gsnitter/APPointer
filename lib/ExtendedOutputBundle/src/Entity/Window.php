<?php
namespace Sni\ExtendedOutputBundle\Entity;

use Symfony\Component\Console\Helper\Table;
use Sni\ExtendedOutputBundle\Entity\Viewport;

/**
 * Class for a window, that displays a buffer.
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class Window
{
    /** @var Buffer */
    private $buffer;

    /** @var Viewport */
    private $viewport;

    /** @var int */
    private $lineNumber;

    /** @var int */
    private $charOffset;

    /** @var string */
    private $mode;

    public function __construct(Viewport $viewport = null, ?Buffer $buffer = null)
    {
        if (!$viewport) {
            # TODO SNI
            $viewport = new Viewport(125, 5, 20, 10);
        }
        $this->viewport = $viewport;
        $this->setBuffer($buffer ? : new Buffer());
        $this->mode = 'wrap';
        $this->charOffset = 0;
        $this->lineNumber = 0;
    }

    public function setBuffer(Buffer $buffer): Window
    {
        $this->buffer = $buffer;
        return $this;
    }

    public function getBuffer(): Buffer
    {
        return $this->buffer;
    }

    /**
     * Resturns the inner area of the window.
     *
     * @return Viewport
     */
    public function getViewport(): Viewport
    {
        return $this->viewport;
    }

    public function setMode($mode): Window
    {
        $this->mode = $mode;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setCharOffset($charOffset): Window
    {
        $this->charOffset = $charOffset;
        return $this;
    }

    public function getCharOffset(): int
    {
        return $this->charOffset;
    }

    public function setLineNumber($lineNumber): Window
    {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }
}
