<?php
declare(strict_types=1);

namespace SniTodos\Entity;

use Symfony\Component\Yaml\Yaml;

use SniTodos\Lib\GoogleClient;
use SniTodos\Lib\Filesystem;

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
        $this->getFileystem();
    }

    public function getFilePath()
    {
        return $this->filePath;
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

        return $this->getLocalFileContent();
    }

    private function getLocalFileContent()
    {
        return $this->fs->getContent($this->filePath);
    }

    public function upload()
    {
        if (!$this->fs->exists($this->filePath)) {
            throw new \Exception("Need to download before uploading {$this->filePath}.");
        }

        if ($this->googleFile->exists()) {
            $this->googleFile->updateContent($this->fs->getContent($this->filePath));
        } else {
            $this->googleFile->create($this->fs->getContent($this->filePath));
        }
    }

    /**
     * @return array
     */
    public function parseYaml(): array
    {
        return Yaml::parse($this->getContent());
    }

    public function updateYaml(array $array): GoogleFileProxy
    {
        $this->googleFile->updateYaml($array);
        return $this;
    }

    public function exists(): bool
    {
        return $this->googleFile->exists();
    }

    public static function getProjectPath(): string
    {
        return GoogleFile::getProjectPath();
    }
}
