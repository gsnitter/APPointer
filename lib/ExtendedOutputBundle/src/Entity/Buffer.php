<?php
namespace Sni\ExtendedOutputBundle\Entity;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class for buffers, which are ConsoleOutputs that write to memory streams.
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class Buffer
{
    /** @var ConsoleOutput */
    private $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput(
            isset($options['verbosity']) ? $options['verbosity'] : ConsoleOutput::VERBOSITY_NORMAL,
            isset($options['decorated']) ? $options['decorated'] : null
        );

        $errorOutput = new StreamOutput(fopen('php://memory', 'w', false));
        $errorOutput->setFormatter($this->output->getFormatter());
        $errorOutput->setVerbosity($this->output->getVerbosity());
        $errorOutput->setDecorated($this->output->isDecorated());

        $reflectedOutput = new \ReflectionObject($this->output);
        $strErrProperty = $reflectedOutput->getProperty('stderr');
        $strErrProperty->setAccessible(true);
        $strErrProperty->setValue($this->output, $errorOutput);

        $reflectedParent = $reflectedOutput->getParentClass();
        $streamProperty = $reflectedParent->getProperty('stream');
        $streamProperty->setAccessible(true);
        $streamProperty->setValue($this->output, fopen('php://memory', 'w', false));
    }

    // TODO SNI: Caching
    public function getLine(int $lineNumber): ?string
    {
        $stream = $this->output->getStream();
        $pos = ftell($stream);
        rewind($stream);
        $lines = explode("\n", stream_get_contents($stream));
        fseek($stream, $pos); 

        return isset($lines[$lineNumber]) ? $lines[$lineNumber] : null;
    }

    public function getOutput()
    {
        return $this->output;
    }
}

