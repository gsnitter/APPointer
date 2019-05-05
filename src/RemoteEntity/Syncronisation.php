<?php declare(strict_types=1);

namespace APPointer\RemoteEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="source_idx", columns={"source"}),
 *     @ORM\Index(name="target_idx", columns={"target"}),
 *     @ORM\Index(name="time_idx", columns={"time"})
 * }, name="syncronisation")
 */
class Syncronisation
{
    /**
     * @var int $id
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $source
     * @ORM\Column(type="string", length=255)
     */
    private $source;

    /**
     * @var string $target
     * @ORM\Column(type="string", length=255)
     */
    private $target;

    /**
     * @var \DateTime $time
     * @ORM\Column(type="datetime")
     */
    private $time;


    private function __construct()
    {
        $this->time = new \DateTime();
    }

    public static function createWithSourceTarget(string $source, string $target) {
        $s = new Syncronisation();
        $s->setSource(trim($source));
        $s->setTarget(trim($target));

        return $s;
    }

    public function setId(int $id):Syncronisation
    {
        $this->id = $id;
        return $this;
    }

    public function getId():?int
    {
        return $this->id;
    }

    public function setSource(string $source):Syncronisation
    {
        $this->source = $source;
        return $this;
    }

    public function getSource():string
    {
        return $this->source;
    }

    public function setTarget(string $target):Syncronisation
    {
        $this->target = $target;
        return $this;
    }

    public function getTarget():string
    {
        return $this->target;
    }

    public function setTime(\DateTime $time):Syncronisation
    {
        $this->time = $time;
        return $this;
    }

    public function getTime():\DateTime
    {
        return $this->time;
    }
}
