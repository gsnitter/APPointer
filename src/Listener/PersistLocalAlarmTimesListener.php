<?php

namespace APPointer\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use APPointer\Entity\Todo;
use APPointer\Entity\AlarmTime;

class PersistLocalAlarmTimesListener implements EventSubscriber
{
    /** @var EntityManagerInterface $em */
    protected $em;

    /**
     * @var bool[] $newDates - Keys are date strings
     */
    protected $newDates = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postUpdate];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->insertIfNotExists($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->insertIfNotExists($args);
    }

    public function insertIfNotExists(LifecycleEventArgs $args)
    {
        $newAlarmTimes = [];

        $entity = $args->getObject();
        if ($entity instanceof Todo) {
            $newAlarmTimeArrays = $entity->getNormalizedAlarmTimes();
            foreach ($newAlarmTimeArrays as $newAlarmTimeArray) {
                $dateString = $newAlarmTimeArray['time'];
                $date = new \DateTime($dateString);

                if (!isset($this->newDates[$dateString]) && !$this->getRepository()->findBy(['date' => $date])) {
                    $newAlarmTime = (new AlarmTime())
                        ->setDate($date)
                        ->setParentTodo($entity);
                    $this->em->persist($newAlarmTime);
                    $newAlarmTimes[] = $newAlarmTime;
                    $this->newDates[$dateString] = true;

                    $newAlarmTime->init();
                }
            }
        }

        if ($newAlarmTimes) {
            $this->em->flush();
        }
    }

    private function getRepository()
    {
        return $this->em->getRepository(AlarmTime::class);
    }
}
