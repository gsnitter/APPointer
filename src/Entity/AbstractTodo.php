<?php

namespace APPointer\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use APPointer\Constraints as CustomAssert;
// use APPointer\Parser\DateParser;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use APPointer\Lib\Normalizer;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for Todos.
 */
class AbstractTodo
{
    /**
     * @var int $globalId
     *
     * @ORM\Column(name="global_id", type="integer", nullable=true)
     */
    protected $globalId;

    /**
     * @var \DateTime $cronExpression
     * @ORM\Column(name="cron_expression", type="string", nullable=true)
     */
    protected $cronExpression;


    /**
     * @var \DateTime $date
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var \DateInterval $displayInterval
     * @ORM\Column(name="display_interval", type="dateinterval", nullable=false)
     */
    protected $displayInterval;

    /**
     * @var string $normalizedAlarmTimes
     * @ORM\Column(name="alarm_times", type="json_array", nullable=true)
     */
    protected $normalizedAlarmTimes;

    /**
     * @var \DateTime $createdAt
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var string text
     * @ORM\Column(name="text", type="text", length=65535, nullable=false)
     */
    protected $text;

    /**
     * @var bool repeatable
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected $repeatable;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = $this->createdAt;
        $this->repeatable = false;

        $this->normalizedAlarmTimes = [];
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt):AbstractTodo
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime $createdAt
     */
    public function getCreatedAt():\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt):AbstractTodo
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt():\DateTime
    {
        return $this->updatedAt;
    }

    public static function setArrayValues(AbstractTodo $todo, array $array): void
    {
        foreach ($array as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (!method_exists($todo, $setter)) {
                if (!$todo instanceOf Todo || !in_array($setter, ['setLastSyncSource', 'setLastSyncTime'])) {
                    throw new \Exception("No setter {$setter} in AbstractTodo-Entity for key {$key} in array " . print_r($array, true));  
                }
            } else {
                $todo->$setter($value);
            }
        }
    }

    public function setGlobalId(?int $globalId):AbstractTodo
    {
        $this->globalId = $globalId;
        return $this;
    }

    public function getGlobalId():?int
    {
        return $this->globalId;
    }

    public function getArrayRepresentation(): array
    {
        return array_filter(get_object_vars($this));
    }

    /**
     * @param string $cronExpression
     * @return $this
     */
    public function setCronExpression(string $cronExpression): Todo
    {
        $this->cronExpression = $cronExpression;
        return $this;
    }

    /**
     * @return string
     */
    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setDate($date):AbstractTodo
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

    /**
     * @param \DateInterval $displayTime
     * @return $this
     */
    public function setDisplayInterval(\DateInterval $displayInterval): AbstractTodo
    {
        $this->displayInterval = $displayInterval;
        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getDisplayInterval(): \DateInterval
    {
        return $this->displayInterval;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text): AbstractTodo
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Tests if $dateString is in the future
     * and $dateString minus $displayTime is in the past.
     *
     * @param DateTime
     * @return bool
     */
    public function isDue(\DateTime $dt = null): bool
    {
        if (!$dt) {
            $dt = new \DateTime();
        }

        // If todo is in past, it is not due
        if (!$this->getDate() || $this->getDate() < $dt) {
            return false;
        }

        $time = clone($this->getDate());
        $time->sub($this->getDisplayInterval());

        // This is tricky: If we say warn me 2 days before Christmas, we expect the warning to be shown
        // on 22.12., 23.12. AND 24.12. Thats why we need to substract another day.
        // As soon as we handle start and end dates of todos, this won't be needed anymore.
        if ($this->date->format('H:i:s') == '23:59:59') {
            $time->sub(new \DateInterval('P1D'));
        }

        return $time < $dt;
    }

    public function hasTime(): bool
    {
        return ($this->getDate()->format('H:i:s') !== '23:59:59');
    }

    public function getNormalizedAlarmTimes(): array
    {
        return $this->normalizedAlarmTimes;
    }

    public function setNormalizedAlarmTimes(array $normalizedAlarmTimes): AbstractTodo
    {
        $this->normalizedAlarmTimes = $normalizedAlarmTimes;
        return $this;
    }

    public function setRepeatable(bool $repeatable): self
    {
        $this->repeatable = $repeatable;
        return $this;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }
}
