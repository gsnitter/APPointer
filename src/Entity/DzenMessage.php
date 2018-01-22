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
 * $message = new DzenMessage($text, (new \DateTime('+1 minutes'))->format('Y-m-d H:i:s'));
 * $message->setType(DzenMEssage::GOOD_NEWS);
 * 
 * require_once(__DIR__ . '/../bootstrap.php');
 * \SniTodos\Lib\DI::getContainer()
 *     ->get('SniTodos\Lib\AtJobs\Installer')
 *     ->install($message);
 */
class DzenMessage
{
    const GOOD_NEWS = 1;
    const NORMAL = 2;
    const BAD_NEWS = 3;

    /** @var string */
    protected $message;

    /** @var string */
    protected $atTimeString;

    /** @var string */
    protected $fgColor;

    /** @var string */
    protected $bgColor;

    public function __construct(string $message, string $atTimeString)
    {
        $this->message = $message;
        $this->atTimeString = $atTimeString;
        $this->setType(DzenMessage::NORMAL);
    }

    public function stringToType(string $string): int
    {
        $string = trim(strtolower($string));

        if (in_array($string, ['good', 'green', 'grün'])) {
            return self::GOOD_NEWS;
        }
        if (in_array($string, ['normal', 'grey', 'grau'])) {
            return self::NORMAL;
        }
        if (in_array($string, ['bad', 'red', 'rot'])) {
            return self::BAD_NEWS;
        }

        return 0;
    }

    public function setType(int $type): DzenMessage
    {
        $this->type = $type;

        switch ($type) {
            case self::GOOD_NEWS:
                $this->setTextColor('green')->setBackgroundColor('darkgreen');
                break;
            case self::BAD_NEWS:
                $this->setTextColor('black')->setBackgroundColor('red');
                break;
            case self::NORMAL:
                $this->setTextColor('#DDDDDD')->setBackgroundColor('#555555');
                break;
            
            default:
                throw new \InvalidArgumentException("DzenMessage of type {$type} unknown.");
                break;
        }

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string - The command to install the at-job
     */
    public function getInstallCommand(): string
    {
        $command  = 'echo "export DISPLAY=$DISPLAY;" echo "\'\n';
        $command .= $this->message . "'";
        $command .= "| dzen2 -p -x '500' -y '30' -sa 'c' -ta 'c' -e 'onstart=uncollapse;button1=exit'";
        $command .= " -w '{$this->getBoxWidth()}' -l '{$this->getBoxHeight()}'";
        $command .= " -bg {$this->bgColor} -fg {$this->fgColor}\"";
        $command .= " | at -t '{$this->getNormalizedAtTimeString()}' 2>&1";

        return $command;
    }

    public function getNormalizedAtTimeString(): string
    {
        return (new \DateTime($this->atTimeString))->format('ymdHi');
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
        return $maxLineLength * 9 + 10;
    }

    private function getBoxHeight(): int
    {
        return count($this->getLines()) + 1;
    }

    public function getLines(): array
    {
        return explode("\n", $this->message);
    }

    /**
     * Returns a hash, that changes, when the corresponding
     * at-Command has to be updated
     */
    public function getHash(): string
    {
        $parts = [
            $this->getMessage(),
            $this->getNormalizedAtTimeString(),
            $this->fgColor,
            $this->bgColor
        ];

        return sha1(implode('', $parts));
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
