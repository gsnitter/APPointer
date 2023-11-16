<?php

namespace APPointer\Listener;

use APPointer\Entity\AlarmTime;
use APPointer\Entity\Todo;
use APPointer\Lib\AlarmTimeManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class PersistLocalAlarmTimesListener implements EventSubscriber
{
    /** @var EntityManagerInterface $em */
    protected $em;

    /** @var AlarmTimeManager $em */
    private $alarmTimeManager;

    /** @var string $timedPopupMessagesPath */
    private $timedPopupMessagesPath;

    /**
     * @var bool[] $newDates - Keys are date strings
     */
    protected $newDates = [];

    public function __construct(EntityManagerInterface $em, AlarmTimeManager $alarmTimeManager)
    {
        $this->em = $em;
        $this->alarmTimeManager = $alarmTimeManager;
        $this->timedPopupMessagesPath = getenv('HOME') . '/.timed_popup_messages';
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
        $counter = 0;

        foreach($newAlarmTimes as $newAlarmTime) {
            $this->createTimedPopupMessage($newAlarmTime, ++$counter);
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
        $filesToDelete = glob("{$this->timedPopupMessagesPath}/*_appointer_{$todo->getLocalId()}_*");

        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    private function createTimedPopupMessage(AlarmTime $alarmTime, int $alarmTimeCounter): void
    {
        $dateString  = $alarmTime->getDate()->format('Y_m_d_H_i_s');
        $todoId      = $alarmTime->getParentTodo()->getLocalId();
        $alarmTimeId = $alarmTime->getId();
        $fileName    = "{$dateString}_appointer_{$todoId}_{$alarmTimeCounter}";

        file_put_contents(
            $this->timedPopupMessagesPath . '/'. $fileName,
            $alarmTime->getParentTodo()->getText()
        );
    }
}
