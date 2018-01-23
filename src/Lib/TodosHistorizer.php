<?php

namespace SniTodos\Lib;

use SniTodos\Lib\TodosFileParser;
use SniTodos\Lib\TodosSaver;

class TodosHistorizer
{
    private $todosFileParser;
    private static $timeString;

    public function __construct(TodosFileParser $todosFileParser, TodosSaver $todosSaver)
    {
        $this->todosFileParser = $todosFileParser;
        $this->todosSaver = $todosSaver;

        if (!self::$timeString) {
            self::$timeString = date('Y-m-d H:i:s');
        }
    }

    public static function setTime(\DateTime $dt)
    {
        self::$timeString = $dt->format('Y-m-d H:i:s');
    }

    public function historize()
    {
        $todos = $this->todosFileParser->getTodos();
        $oldTodos = [];

        foreach ($todos as $key => $todo) {
            if ($todo->getNormalizedDateString() < self::$timeString) {
                $oldTodos[] = $todo;
                unset($todos[$key]);
            }
        }

        $this->todosSaver->save('todos.yml', array_values($todos));
        $this->todosSaver->append('todos_history.yml', $oldTodos);
    }
}
