<?php

namespace SniTodos\Lib;

use SniTodos\Parser as Parser;
use SniTodos\Entity\Todo;
use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;

class Filesystem extends BaseFileSystem
{

    // Unfortunately, F. Potencier refuses to wrap file_get_contents,
    // though for testing, it is very handy.
    public function getContent(string $path): string
    {
        return file_get_contents($path);
    }
}
