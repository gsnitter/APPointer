<?php

namespace APPointer\Lib;

use APPointer\Entity\AlarmTime;
use APPointer\Entity\Todo;
use Doctrine\ORM\EntityManagerInterface;

class AlarmTimeManager
{
    /** @var EntityManagerInterface $em */
    protected $em;
    /** @var  array - keys with DateTime-Strings */
    protected $newDates;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addAlarmTimes(Todo $todo): array
    {
        $newAlarmTimes = [];
        $newAlarmTimeArrays = $todo->getNormalizedAlarmTimes();
        foreach ($newAlarmTimeArrays as $newAlarmTimeArray) {
            $dateString = $newAlarmTimeArray['time'];
            $date = new \DateTime($dateString);

            if (!isset($this->newDates[$dateString]) && !$this->em->getRepository(AlarmTime::class)->findBy(['date' => $date])) {
                $newAlarmTime = (new AlarmTime())
                    ->setDate($date)
                    ->setParentTodo($todo);
                $this->em->persist($newAlarmTime);
                $newAlarmTimes[] = $newAlarmTime;
                $this->newDates[$dateString] = true;

                $atJobId = $newAlarmTime->init();
                $newAlarmTime->setAtJobId($atJobId);
            }
        }

        return $newAlarmTimes;
    }
}
