<?php
namespace Sni\ExtendedOutputBundle\Service;

use Sni\ExtendedOutputBundle\Entity\Tab;
use Symfony\Component\Console\Output\StreamOutput;
use Sni\ExtendedOutputBundle\Entity\Window;

/**
 * Base class for console output with tabs containing windows and buffers.
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class ExtendedOutput
{
    /** Tab[] $tabs */
    private $tabs;

    /** Tab $activeTab */
    private $activeTab;

    /** WindowRenderer $windowRenderer */
    private $windowRenderer;

    public function __construct(WindowRenderer $windowRenderer)
    {
        $this->windowRenderer = $windowRenderer;
        $tab = new Tab();
        $this->addTab($tab);
    }

    public function addTab(Tab $tab)
    {
        $this->tabs[] = $tab;
        $this->activeTab = $tab;
    }

    // TODO SNI: Am besten alles an den activeOutput weitergeben
    public function write($messages, $newline = false, $options = 0)
    {
        $this->getActiveOutput()->write($messages, $newline, $options);
    }

    public function writeln($messages, $options = 0)
    {
        $this->getActiveOutput()->writeln($messages, $options);
    }

    public function getActiveOutput(): StreamOutput
    {
        return $this->getActiveWindow()->getBuffer()->getOutput();
    }

    public function getActiveWindow(): Window
    {
        return $this->activeTab->getActiveWindow();
    }

    public function renderActiveWindow(): void
    {
        $this->windowRenderer->render($this->getActiveWindow());
    }
}
