<?php

namespace SniTodos\Lib;

use SniTodos\Entity\Todo;
use SniTodos\Entity\GoogleFile;
use SniTodos\Entity\GoogleFileProxy;

class TodosFileParser
{
    /** @param GoogleFileProxy $file */
    private $file;

    public function getGoogleFile(): GoogleFileProxy
    {
        if (!$this->file) {
            $this->file = GoogleFile::getInstance('todos.yml');
        }

        return $this->file;
    }

    public function setGoogleFile(GoogleFileProxy $file): TodosFileParser
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return Todo[]
     */
    public function getTodos(): array
    {
        $todoArrays = $this->getGoogleFile()->parseYaml();

        return array_map(function($todoArray) {
            return Todo::createFromArray($todoArray);
        }, $todoArrays);
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
