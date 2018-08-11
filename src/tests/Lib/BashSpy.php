<?php

namespace APPointer\tests\Lib;

use APPointer\Lib\Bash;

class BashSpy extends Bash
{
    private $mappings = [];
    private $singleMappings = [];
    private $calls = [];

    public function addMapping(string $command, string $result): void
    {
        $this->mappings[$command] = $result;
    }

    public function addSingleMapping(string $command, string $result): void
    {
        $this->singleMappings[$command][] = $result;
    }

    public function exec(string $command): string
    {
        $this->calls[] = $command;
        if (isset($this->singleMappings[$command])) {
            return array_shift($this->singleMappings[$command]);
        } elseif (isset($this->mappings[$command])) {
            return $this->mappings[$command];
        } else {
            return '';
        }
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function reset(): void
    {
        $this->calls = [];
        $this->mappings = [];
    }
}
