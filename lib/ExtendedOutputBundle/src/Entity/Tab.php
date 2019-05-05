<?php
namespace Sni\ExtendedOutputBundle\Entity;

/**
 * Class for tabs, that contain windows.
 * @author Steffen Nitter <steffen_nitter@gmx.de>
 */
class Tab
{
    /** @var Window[] */
    private $windows;

    /** @var Window */
    private $activeWindow;

    public function __construct()
    {
        $this->addWindow(new Window());
    }

    public function addWindow(Window $window)
    {
        $this->windows[] = $window;
        $this->activeWindow = $window;
    }

    public function getActiveWindow(): Window
    {
        return $this->activeWindow;
    }
}

