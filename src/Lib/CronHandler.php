<?php declare(strict_types=1);

namespace APPointer\Lib;

use APPointer\Entity\Todo;
use Doctrine\ORM\EntityManagerInterface;

class CronHandler
{
    /** @var EntityManagerInterface $localEm */
    private $localEm;

    public function __construct(EntityManagerInterface $localEm)
    {
        $this->localEm  = $localEm;
    }

    public function resetDateStrings(): void
    {
        $todos = $this->localEm->getRepository(Todo::class)->findOutdatedCronTodos();

        foreach ($todos as $todo) {
            $cron = \Cron\CronExpression::factory($todo->getCronExpression());
            $nextDate = $cron->getNextRunDate();
            $todo
                ->setDateString($nextDate->format('Y-m-d H:i:s'))
                ->setDate($nextDate);
        }

        $this->localEm->flush();
    }
}
