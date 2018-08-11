<?php

namespace APPointer\Lib\AtJobs;

use APPointer\Entity\DzenMessage;

class Installer
{
    public function install(DzenMessage $dzenMessage): int
    {
        exec($dzenMessage->getInstallCommand(), $output, $exitStatus);

        preg_match('@job (\d+)@', join("\n", $output), $matches);
        return intval($matches[1]) ?? 0;
    }

    public function remove(int $id)
    {
        exec("atrm {$id}");
    }

    public function getAtIds(): array
    {
        $idsString = `at -l`;
        $idStrings = array_filter(explode("\n", $idsString));

        return array_map('intval', $idStrings);
    }
}
