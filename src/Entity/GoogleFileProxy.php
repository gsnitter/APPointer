<?php
declare(strict_types=1);

namespace SniTodos\Entity;

use Symfony\Component\Yaml\Yaml;

use SniTodos\Lib\GoogleClient;
use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Caches GoogleFiles to the file system. 
 *
 * @class GoogleFile
 */
class GoogleFileProxy {

    /** @var GoogleFile $googleFile */
    protected $googleFile;

    /** @var string $filePath */
    protected $filePath;

    /** @var Filesystem $fs */
    protected $fs;

    public function __construct(string $fileName, GoogleFile $googleFile = null)
    {
        $this->setGoogleFile($googleFile ?? new GoogleFile($fileName));
        $this->filePath = GoogleFile::getFileCache() . '/' . $fileName;
    }

    public function getFileystem()
    {
        if (!$this->fs) {
            $this->fs = new Filesystem();
        }

        return $this->fs;
    }

    public function setFileystem(Filesystem $fs): GoogleFileProxy
    {
        $this->fs = $fs;
        return $this;
    }

    public function setGoogleFile(GoogleFile $googleFile)
    {
        $this->googleFile = $googleFile;
    }

    public function clearCache(): GoogleFileProxy
    {
        $this->fs->remove([$this->filePath]);
        return $this;
    }

    public function setContent(string $newContent): GoogleFileProxy
    {
        $this->fs->dumpFile($this->filePath, $newContent);
        return $this;
    }

    public function getContent(): string
    {
        if (!$this->fs->exists($this->filePath)) {
            $content = $this->googleFile->getContent();
            $this->fs->dumpFile($this->filePath, $content);
            return $content;
        }

        return file_get_contents($this->filePath);
    }
}
