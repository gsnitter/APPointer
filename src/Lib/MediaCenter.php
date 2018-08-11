<?php

namespace APPointer\Lib;

use APPointer\Lib\Bash;
use APPointer\Lib\DI;
/**
 * @class MediaCenter
 *
 * Mounts the MediaCenter locally and manages its files.
 */
class MediaCenter {

    private $bash;

    public function __construct(Bash $bash)
    {
        $this->bash = $bash;
    }

    public static function getDriveLocation(): string
    {
        return DI::getHomePath() . '/Mediacenter';
    }

    public function isMounted(): bool
    {
        $fileSystemName = trim($this->bash->exec("stat -f -c '%T' ~/Mediacenter"));
        return $fileSystemName == 'fuseblk';
    }

    /**
     * Tries to mount the GMX-MediaCenter, if not already mounted.
     * Return true on success, false on failure.
     */
    public function mount(): bool
    {
        if (!$this->isMounted()) {
            $this->bash->exec('mount ' . self::getDriveLocation());
        }

        return $this->isMounted();
    }

    public function umount(): bool
    {
        if ($this->isMounted()) {
            $this->bash->exec('umount ' . self::getDriveLocation());
        }

        return !$this->isMounted();
    }
}
