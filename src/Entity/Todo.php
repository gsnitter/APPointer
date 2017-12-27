<?php

namespace SniTodos\Entity;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use SniTodos\Parser\DateParser;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use SniTodos\Lib\Normalizer;

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
    private $dateString;

    /**
     * @var string $normalizedDateString
     */
    private $normalizedDateString;

    /**
     * @var string $alarmTime
     */
    private $alarmTime;

    /**
     * @var string $normalizedAlarmTime
     */
    private $normalizedAlarmTime;

    /**
     * @var string text
     */
    private $text;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('dateString', new Assert\NotBlank());

        $normalizer = Normalizer::getInstance();
        foreach($normalizer->getPropertyParsers() as $prop => $parser) {
            $metadata->addPropertyConstraint($prop, new Assert\Callback([$parser, 'validate']));
        }
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
     * @param string $alarmTime
     * @return $this
     */
    public function setAlarmTime(string $alarmTime): Todo
    {
        $this->alarmTime = $alarmTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlarmTime(): string
    {
        return $this->alarmTime;
    }

    /**
     * @param string $alarmTime
     * @return $this
     */
    public function setNormalizedAlarmTime(string $alarmTime): Todo
    {
        $this->normalizedAlarmTime = $alarmTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getNormalizedAlarmTime(): string
    {
        return $this->normalizedAlarmTime;
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
     * and $dateString minus $alarmTime is in the past.
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

        $time->sub(new \DateInterval($this->getNormalizedAlarmTime()));

        // This is tricky: If we say warn me 2 days before Christmas, we expect the warning to be shown
        // on 22.12., 23.12. AND 24.12. Thats why we need to substract another day.
        if (substr($this->getNormalizedDateString(), -8) == '23:59:59') {
            $time->sub(new \DateInterval('P1D'));
        }

        return ($time->format('Y-m-d H:i:s') < $dt->format('Y-m-d H:i:s'));
    }

    public function hasTime(): bool
    {
        return (substr($this->getNormalizedDateString(), -8) == '23:59:59');
    }
}
