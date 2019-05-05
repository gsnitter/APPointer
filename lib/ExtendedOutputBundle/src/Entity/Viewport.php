<?php
namespace Sni\ExtendedOutputBundle\Entity;

class Viewport
{
    public function __construct(int $x, int $y, int $width, int $height)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function setX($x):Viewport
    {
        $this->x = $x;
        return $this;
    }

    public function getX():int
    {
        return $this->x;
    }

    public function setY($y):Viewport
    {
        $this->y = $y;
        return $this;
    }

    public function getY():int
    {
        return $this->y;
    }

    public function setWidth($width):Viewport
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth():int
    {
        return $this->width;
    }

    public function setHeight($height):Viewport
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight():int
    {
        return $this->height;
    }
}
