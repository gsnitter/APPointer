<?php

namespace APPointer\RemoteEntity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use APPointer\Entity\AbstractTodo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Remote version of Todos.
 * Also it adds extra properties like nextDate.
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="last_sync_time_idx", columns={"last_sync_time"})
 * }, name="todo")
 */
class RemoteTodo extends AbstractTodo
{
    /**
     * For local todos, the globalId is no auto increment field.
     * @var int $global_id
     *
     * @ORM\Column(name="global_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $globalId;

    /**
     * @var \DateTime $lastSyncTime
     * @ORM\Column(name="last_sync_time", type="datetime", nullable=false)
     */
    protected $lastSyncTime;

    /**
     * Client, from which the current version was uploaded.
     * @var string $lastSyncTime
     * @ORM\Column(name="last_sync_source", type="string", length=255, nullable=false)
     */
    protected $lastSyncSource;

    public static function createFromArray(array $array): RemoteTodo
    {
        $todo = new RemoteTodo();
        self::setArrayValues($todo, $array);
        return $todo;
    }

    public function setLastSyncTime($lastSyncTime):RemoteTodo
    {
        $this->lastSyncTime = $lastSyncTime;
        return $this;
    }

    public function getLastSyncTime():\DateTime
    {
        return $this->lastSyncTime;
    }

    public function setLastSyncSource($lastSyncSource):RemoteTodo
    {
        $this->lastSyncSource = $lastSyncSource;
        return $this;
    }

    public function getLastSyncSource():string
    {
        return $this->lastSyncSource;
    }
}
