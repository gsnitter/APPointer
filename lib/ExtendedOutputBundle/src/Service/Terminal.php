<?php
namespace Sni\ExtendedOutputBundle\Service;

class Terminal
{
    public function writeLineToPos(string $x, string $y, string $line): void
    {
        passthru("tput cup ${y} ${x} && echo '${line}'");
    }
}

