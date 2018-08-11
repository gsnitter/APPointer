<?php

namespace APPointer\Lib;

use APPointer\Entity\GoogleFile;
use APPointer\Entity\Todo;

class TodosSaver
{
    private function convertToArray(array $todos): array
    {
        return array_map(function($todo) {
            return ($todo instanceof Todo)? $todo->getArrayRepresentation() : $todo;
        }, $todos);
    }

    public function save(string $googleFileName, array $todos): bool
    {
        $file = GoogleFile::getInstance($googleFileName);
        $file->updateYaml($this->convertToArray($todos));
        return true;
    }

    public function append(string $googleFileName, array $todos): bool
    {
        $file = GoogleFile::getInstance($googleFileName);
        $file->appendYaml($this->convertToArray($todos));
        return true;
    }
}
