<?php

namespace APPointer\Lib;

use APPointer\Entity\Todo;
use APPointer\Entity\GoogleFile;
use APPointer\Entity\GoogleFileProxy;
use APPointer\Lib\DI;
use APPointer\Lib\Filesystem;

class TodosFileParser
{
    /** @var Filesystem */
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function getYaml(): array
    {
        return $this->fs->loadYaml(DI::getLocalPath());
    }

    /**
     * @return Todo[]
     */
    public function getTodos(): array
    {
        return array_map(function($todoArray) {
            // Should never get into the todos file, but once it did.
            if ($todoArray['normalizedDisplayTime'] == null) {
                $error = 'Error: No normlaizedDisplayTime in the record: ' . print_r($todoArray, true);
                throw new \Exception($error);
            }
            return Todo::createFromArray($todoArray);
        }, $this->getYaml());
    }

    /**
     * @return Todo[]
     */
    public function getDueTodos(): array
    {
        $todosArray = $this->getTodos();

        return array_filter($todosArray, function($todo) {
            return $todo->isDue();
        });
    }

    public function getAlarmTimes(): array
    {
        $todos = $this->getTodos();

        $alarmTimes = array_reduce($todos, function($alarmTimes, $todo) {
            $toAdds = $todo->getNormalizedAlarmTimes();
            array_walk($toAdds, function(&$toAdd) use ($todo) {
                $toAdd['message'] = $todo->getText();
            });
            return array_merge($alarmTimes, $toAdds);
        }, []);

        return $alarmTimes;
    }
}
