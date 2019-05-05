<?php
namespace Sni\ExtendedOutputBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sni\ExtendedOutputBundle\Service\Terminal;
use Sni\ExtendedOutputBundle\Entity\Window;

class WindowRenderer
{
    private $container;
    private $terminal;

    public function __construct(ContainerInterface $container, Terminal $terminal)
    {
        $this->container = $container;
        $this->terminal = $terminal;
    }

    public function render(Window $window): void
    {
        $projector = $this->container->get('extended.output.text.projector.' . $window->getMode());

        $lines = $projector->getLines(
            $window->getBuffer(),
            $window->getViewport(),
            $window->getLineNumber(),
            $window->getCharOffset()
        );
        $viewport = $window->getViewport();
        foreach ($lines as $index => $line) {
            $this->terminal->writeLineToPos($viewport->getX(), $viewport->getY() + $index, $line);
        }
    }
}
