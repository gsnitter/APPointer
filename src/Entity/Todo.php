<?php

namespace APPointer\Entity;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use APPointer\Constraints as CustomAssert;
// use APPointer\Parser\DateParser;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use APPointer\Lib\Normalizer;

/**
 * Gets an array of properties like 'dateString' etc., validates and normalizes them.
 * Also it adds extra properties like nextDate.
 *
 * @class Todo
 */
class Todo {

    /**
     * @var string $dateString
     */
    private $dateString = '';

    /**
     * @var string $normalizedDateString
     */
    private $normalizedDateString = '';

    /**
     * @var string $displayTime
     */
    private $displayTime;

    /**
     * @var string $normalizedDisplayTime
     */
    private $normalizedDisplayTime;

    /**
     * @var string|array $alarmTimes
     */
    private $alarmTimes;

    /**
     * @var string $normalizedAlarmTimes
     */
    private $normalizedAlarmTimes;

    /**
     * @var string $normalizedCreatedAt
     */
    private $normalizedCreatedAt;

    /**
     * @var string text
     */
    private $text;

    public function __construct()
    {
        $this->normalizedCreatedAt = date('Y-m-d H:i:s');
        $this->normalizedAlarmTimes = [];
    }

    public function setNormalizedCreatedAt(string $dateString): Todo
    {
        $this->normalizedCreatedAt = $dateString;
        return $this;
    }

    public function getNormalizedCreatedAt(): string
    {
        return $this->normalizedCreatedAt;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('dateString', new Assert\NotBlank(['groups' => ['Add', 'Default']]));
        $constraint1 = new CustomAssert\DateStringNormalizer(['groups' => ['Add']]);
        $constraint2 = new CustomAssert\DisplayTimeNormalizer(['groups' => ['Add']]);
        $constraint3 = new CustomAssert\AlarmTimesNormalizer(['groups' => ['Add']]);
        $constraint1->path = 'dateString';
        $constraint2->path = 'displayTime';
        $constraint3->path = 'alarmTimes';
        // $constraint->addGroup('bla');
        $metadata->addConstraint($constraint1);
        $metadata->addConstraint($constraint2);
        $metadata->addConstraint($constraint3);
        
        // TODO SNI: Ggf. recyceln
        // $normalizer = Normalizer::getInstance();
        // foreach($normalizer->getPropertyParsers() as $prop => $parser) {
            // $metadata->addPropertyConstraint($prop, new Assert\Callback([$parser, 'validate']));
        // }
    }

    public static function createFromArray(array $array): Todo
    {
        $todo = new Todo();

        foreach ($array as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (!method_exists($todo, $setter)) {
                throw new \Exception("No setter {$setter} in Todo-Entity for key {$key} in array " . print_r($array, true));  
            } else {
                $todo->$setter($value);
            }
        }

        return $todo;
    }

    public function getArrayRepresentation(): array
    {
        // $this->getCreatedAt();
        return get_object_vars($this);
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
     * @param string $dateString
     * @return $this
     */
    public function setNormalizedDateString(string $dateString): Todo
    {
        $this->normalizedDateString = $dateString;
        return $this;
    }

    /**
     * @return string
     */
    public function getNormalizedDateString(): string
    {
        return $this->normalizedDateString;
    }

    /**
     * @param string $displayTime
     * @return $this
     */
    public function setDisplayTime(string $displayTime): Todo
    {
        $this->displayTime = $displayTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayTime(): string
    {
        return $this->displayTime;
    }

    /**
     * @param string $displayTime
     * @return $this
     */
    public function setNormalizedDisplayTime(string $displayTime): Todo
    {
        $this->normalizedDisplayTime = $displayTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getNormalizedDisplayTime(): string
    {
        return $this->normalizedDisplayTime;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text): Todo
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
     * @param string[] $tags
     * @return $this
     */
    public function setTags(array $tags): Todo
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
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

        $time = new \DateTime($this->getNormalizedDateString());

        // If todo is in past, it is not due
        if ($time->format('Y-m-d H:i:s') < $dt->format('Y-m-d H:i:s')) {
            return false;
        }

        $time->sub(new \DateInterval($this->getNormalizedDisplayTime()));

        // This is tricky: If we say warn me 2 days before Christmas, we expect the warning to be shown
        // on 22.12., 23.12. AND 24.12. Thats why we need to substract another day.
        if (substr($this->getNormalizedDateString(), -8) == '23:59:59') {
            $time->sub(new \DateInterval('P1D'));
        }

        return ($time->format('Y-m-d H:i:s') < $dt->format('Y-m-d H:i:s'));
    }

    public function isDueToday(): bool
    {
        $time = new \DateTime($this->getNormalizedDateString());
        // echo "\nVergleiche {$time->format('Y-m-d')} und " . date('Y-m-d') . "\n";
        return ($time->format('Y-m-d') == date('Y-m-d'));
    }

    public function hasTime(): bool
    {
        return (substr($this->getNormalizedDateString(), -8) == '23:59:59');
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

    public function getNormalizedAlarmTimes(): array
    {
        return $this->normalizedAlarmTimes;
    }

    public function setNormalizedAlarmTimes(array $normalizedAlarmTimes): Todo
    {
        $this->normalizedAlarmTimes = $normalizedAlarmTimes;
        return $this;
    }
}
