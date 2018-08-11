<?php

namespace APPointer\tests\Lib;

use APPointer\Lib\Filesystem;

class FilesystemTest extends Filesystem
{
    private $fileContent = [];

    // Symfony did not yet include type hints.
    public function dumpFile($path, $content)
    {
        $this->fileContent[$path] = $content;
    }

    public function getContent(string $path): string
    {
        if (!isset($this->fileContent[$path])) {
            throw new \InvalidArgumentException("FilesystemTest: path {$path} not initalized");
        }

        return $this->fileContent[$path];
    }
}
