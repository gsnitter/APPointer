<?php declare(strict_types=1);

namespace APPointer\Lib;

use APPointer\Entity\Todo;
use APPointer\Parser\AlarmTimesParser;
use APPointer\Repository\TodoRepository;

class CronHandler
{
    /** @var TodoRepository $todoRepository */
    private $todoRepository;

    /** @var AlarmTimesParser $parser */
    private $parser;

    public function __construct(TodoRepository $todoRepository, AlarmTimesParser $parser)
    {
        $this->todoRepo = $todoRepository;
        $this->parser   = $parser;
    }

    public function resetDateStrings(): void
    {
        $todos = $this->todoRepo->findOutdatedCronTodos();

        foreach ($todos as $todo) {
            $cron = \Cron\CronExpression::factory($todo->getCronExpression());
            $nextDate = $cron->getNextRunDate();
            $todo
                ->setDateString($nextDate->format('Y-m-d H:i:s'))
                ->setDate($nextDate);

            $this->resetAlarmTimes($todo);
        }
    }

    public function resetAlarmTimes(Todo $todo): void
    {
        $this->parser->setDate($todo->getDate());
        $normalizedAlarmTimes = $this->parser->normalize($todo->getAlarmTimes());

        $todo->setNormalizedAlarmTimes($normalizedAlarmTimes);
    }
}
