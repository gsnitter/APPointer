<?php

namespace APPointer\Lib;

use APPointer\Parser as Parser;
use APPointer\Entity\Todo;
use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;
use Symfony\Component\Yaml\Yaml;

class Filesystem extends BaseFileSystem
{

    // Unfortunately, F. Potencier refuses to wrap file_get_contents,
    // though for testing, it is very handy.
    public function getContent(string $path): string
    {
        return file_get_contents($path);
    }

    public function dumpYaml(string $path, array $array)
    {
        $this->dumpFile($path, Yaml::dump($array));
    }

    public function loadYaml(string $path): array
    {
        $content = $this->getContent($path);
        return $content ?  Yaml::parse($this->getContent($path)) : [];
    }
}
