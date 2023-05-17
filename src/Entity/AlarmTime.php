<?php

namespace APPointer\Entity;

use Doctrine\ORM\Mapping as ORM;
use APPointer\Lib\DI;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="date_idx", columns={"date"})
 * }, name="alarm_time")
 */
class AlarmTime
{
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime $date
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var Todo $parentTodo
     * @ORM\ManyToOne(targetEntity="Todo", inversedBy="alarmTimeEntities")
     * @ORM\JoinColumn(name="todo_id", referencedColumnName="local_id")
     */
    protected $parentTodo;

    public function setId(int $id): AlarmTime
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param Todo $parentTodo
     * @return $this
     */
    public function setParentTodo($parentTodo):AlarmTime
    {
        $this->parentTodo = $parentTodo;
        return $this;
    }

    /**
     * @return Todo $parentTodo
     */
    public function getParentTodo():Todo
    {
        return $this->parentTodo;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setDate($date):AlarmTime
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \DateTime $date
     */
    public function getDate():?\DateTime
    {
        return $this->date;
    }

    private function getDateString(): string
    {
        return $this->date->format('H:i d.m.Y');
    }

    /**
     * Initalizes an at-Job for the given date.
     * Returns the new at job id or null if not successfull.
     */
    public function init(): ?int
    {
        $command = 'php ' . DI::getProjectPath() . '/bin/console appoint --show-alarm-times';
        $dateString = $this->getDateString();

        // Anscheinend schreibt at alles nach stderr, deshalb dieser Umweg:
        $result = shell_exec("echo '{$command }' | at '{$dateString}' 2>&1");

        if (preg_match('@.*job (\d+) at .*@', $result, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function removeAtJob(): void
    {
        if ($this->getAtJobId()) {
            shell_exec("atrm '{$this->getAtJobId()}");
        }
    }
}
