<?php
declare(strict_types=1);

namespace SniTodos\Entity;

/**
 * Example Usage:
 * $text = <<<EOT
 * ÜBERSCHRIFT
 * 
 * Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.
 * Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
 * Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.
 * Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.
 * In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis mollis pretium.
 * EOT;
 * $message = new DzenMessage($text);
 * $message
 * ->showAt('now +1 minutes');
 */
class DzenMessage
{
    const GOOD_NEWS = 0;
    const BAD_NEWS = 1;

    /** @var string */
    protected $message;

    /** @var string */
    protected $fgColor = "#DDDDDD";

    /** @var string */
    protected $bgColor = "#666666";

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function setType(int $type): DzenMessage
    {
        switch ($type) {
            case self::GOOD_NEWS:
                $this->setTextColor('green')->setBackgroundColor('darkgreen');
                break;
            case self::BAD_NEWS:
                $this->setTextColor('black')->setBackgroundColor('red');
                break;
            
            default:
                throw new \InvalidArgumentException("DzenMessage of type {$type} unknown.");
                break;
        }

        return $this;
    }

    public function showAt(string $timeString): int
    {

        $command  = 'echo "export DISPLAY=$DISPLAY;" echo "\'';
        $command .= $this->message . "'";
        $command .= "| dzen2 -p -x '500' -y '30' -sa 'c' -ta 'c' -e 'onstart=uncollapse;button1=exit'";
        $command .= " -w '{$this->getBoxWidth()}' -l '{$this->getBoxHeight()}'";
        $command .= " -bg {$this->bgColor} -fg {$this->fgColor}\"";
        $command .= ' | at "now +1 minutes" 2>&1';

        exec($command, $output, $exitStatus);
        return $exitStatus;
    }

    public function setTextColor(string $fgColor): DzenMessage
    {
        $this->fgColor = $fgColor;
        return $this;
    }

    public function setBackgroundColor($bgColor): DzenMessage
    {
        $this->bgColor = $bgColor;
        return $this;
    }

    /**
     * We only estimate the box width, so we don't have to struggle with fonts.
     */
    private function getBoxWidth(): int
    {
        $maxLineLength = array_reduce($this->getLines(), function($max, $line) {
            $lineLength = strlen($line);
            return $lineLength > $max ? $lineLength : $max;
        }, 0);
        // TODO SNI
        return $maxLineLength * 9;
    }

    private function getBoxHeight(): int
    {
        return count($this->getLines()) - 1;
    }

    public function getLines(): array
    {
        return explode("\n", $this->message);
    }
}
