<?php declare(strict_types=1);

namespace APPointer\RemoteEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="time_idx", columns={"time"}),
 *     @ORM\Index(name="event_idx", columns={"event"})
 * }, name="log")
 */
class Log
{
    /**
     * @var int $id
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime $time
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @var string $event
     * @ORM\Column(type="string", length=255)
     */
    private $event;

    public function __construct(string $event)
    {
        $this->setEvent($event);
        $this->setTime(new \DateTime());
    }

    public function setId(int $id):Log
    {
        $this->id = $id;
        return $this;
    }

    public function getId():int
    {
        return $this->id;
    }

    public function setTime($time):Log
    {
        $this->time = $time;
        return $this;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function setEvent($event):Log
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent():string
    {
        return $this->event;
    }
}
