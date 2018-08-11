<?php

namespace APPointer\Lib;

/**
 * Main purpose of this class is to be replaced by BashSpy in tests.
 */
class Bash
{
    // public function exec($command: string): string
    public function exec(string $command): string
    {
        return exec($command);
    }
}
