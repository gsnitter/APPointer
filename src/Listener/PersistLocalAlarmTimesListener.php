<?php

namespace APPointer\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use APPointer\Entity\Todo;
use APPointer\Entity\AlarmTime;
use APPointer\Lib\AlarmTimeManager;

class PersistLocalAlarmTimesListener implements EventSubscriber
{
    /** @var EntityManagerInterface $em */
    protected $em;

    /** @var AlarmTimeManager $em */
    private $alarmTimeManager;

    /**
     * @var bool[] $newDates - Keys are date strings
     */
    protected $newDates = [];

    public function __construct(EntityManagerInterface $em, AlarmTimeManager $alarmTimeManager)
    {
        $this->em = $em;
        $this->alarmTimeManager = $alarmTimeManager;
    }

    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postUpdate];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Todo) {
            $this->recreateTimedPopupMessages($entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Todo) {
            $this->recreateTimedPopupMessages($entity);
        }
    }

    public function preRemove($args)
    {
        $alarmTime = $args->getObject();

        if ($alarmTime instanceof AlarmTime) {
            $this->recreateTimedPopupMessages($alarmTime->getParentTodo());
        }
    }

    private function recreateTimedPopupMessages(Todo $todo)
    {
        $this->deleteTimedPopupMessages($todo);

        $newAlarmTimes = $this->alarmTimeManager->addAlarmTimes($todo);

        foreach($newAlarmTimes as $newAlarmTime) {
            $this->createTimedPopupMessage($newAlarmTime);
        }

        if ($newAlarmTimes) {
            $this->em->flush();
        }
    }

    /**
     * Delete files from '~/.timed_popup_messages' named '*_todo_{$todo->getId()}_*'.
     */
    private function deleteTimedPopupMessages(Todo $todo): void
    {
        $filesToDelete = glob("*_{$todo->getLocalId()}");

        foreach ($filesToDelete as $file) {
            unlink($this->timedPopupMessagesPath . '/' . $file);
        }
    }

    private function createTimedPopupMessage(AlarmTime $alarmTime): void
    {
        $dateString = $alarmTime->getDate()->format('Y.m.d_H:i:s');
        $todoId     = $alarmTime->getParentTodo()->getLocalId();
        $fileName   = "{$dateString}_todo_{$todoId}";

        file_put_contents(
            "{$this->timedPopupMessagesPath}/{$fileName}",
            $alarmTime->getParentTodo()->getText()
        );
    }
}
