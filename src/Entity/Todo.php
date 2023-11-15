<?php

namespace APPointer\Entity;

// use APPointer\Parser\DateParser;
use APPointer\Constraints as CustomAssert;
use APPointer\Lib\Normalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Gets an array of properties like 'dateString' etc., validates and normalizes them.
 * Also it adds extra properties like nextDate.
 * @ORM\Entity(repositoryClass="APPointer\Repository\TodoRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="date_idx", columns={"date"}),
 *     @ORM\Index(name="display_interval_idx", columns={"display_interval"})
 * }, name="todo")
 */
class Todo extends AbstractTodo
{
    /**
     * @var int $localId
     *
     * @ORM\Column(name="local_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $localId;

    /**
     * @var string $dateString
     * @ORM\Column(type="text", length=255, nullable=false)
     */
    private $dateString = '';

    /**
     * @var string $displayIntervalString
     */
    private $displayIntervalString;

    /**
     * @ORM\Column(name="alarm_times_input", type="simple_array", nullable=true)
     * @var string|array $alarmTimes
     */
    private $alarmTimes;

    /**
     * @var Collection $alarmTimeEntities
     * @ORM\OneToMany(targetEntity="AlarmTime", mappedBy="parentTodo")
     */
    private $alarmTimeEntities;

    public function __construct()
    {
        $this->alarmTimeEntities = new ArrayCollection();
        parent::__construct();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('dateString', new Assert\NotBlank(['groups' => ['Add', 'Default']]));

        $constraint1 = new CustomAssert\DateStringNormalizer(['groups' => ['Add']]);
        $constraint2 = new CustomAssert\DisplayIntervalNormalizer(['groups' => ['Add']]);
        $constraint3 = new CustomAssert\AlarmTimesNormalizer(['groups' => ['Add']]);

        $constraint1->path = 'dateString';
        $constraint2->path = 'displayInterval';
        $constraint3->path = 'alarmTimes';

        $metadata->addConstraint($constraint1);
        $metadata->addConstraint($constraint2);
        $metadata->addConstraint($constraint3);
    }

    public static function createFromArray(array $array): Todo
    {
        $todo = new Todo();
        self::setArrayValues($todo, $array);
        return $todo;
    }

    public function setLocalId(int $localId): Todo
    {
        $this->localId = $localId;
        return $this;
    }

    public function getLocalId(): ?int
    {
        return $this->localId;
    }

    /**
     * @param string $dateString
     * @return $this
     */
    public function setDateString(string $dateString): Todo
    {
        $this->dateString = $dateString;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->dateString;
    }

    /**
     * @param string $displayIntervalString
     * @return $this
     */
    public function setDisplayIntervalString(string $displayIntervalString): Todo
    {
        $this->displayIntervalString = $displayIntervalString;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayIntervalString(): string
    {
        return (string) $this->displayIntervalString;
    }

    public function getAlarmTimes()
    {
        return $this->alarmTimes;
    }

    public function setAlarmTimes($alarmTimes): Todo
    {
        $this->alarmTimes = $alarmTimes;
        return $this;
    }

    public function getAlarmTimeEntities(): Collection
    {
        return $this->alarmTimeEntities;
    }
}
