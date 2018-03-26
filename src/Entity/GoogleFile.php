<?php
declare(strict_types=1);

namespace SniTodos\Entity;

use Symfony\Component\Yaml\Yaml;

use Google_Service_Drive_DriveFile;
use Google_Client;

use SniTodos\Lib\GoogleClient;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Mainly used as a facade for Google_Service_Drive_Resource_Files and alikes.
 *
 * @class GoogleFile
 */
class GoogleFile {

    // @var GoogleFileProxy[] $instance;
    private static $instance = [];

    // @var string $fileCache - Path to where the GoogleFiles will be downloaded.
    private static $fileCache;

    // @var Google_Service_Drive $service
    private static $service;

    // @var string $fileName
    private $fileName;

    // @var string $content
    private $content;

    // @var string $id
    private $id;

    // @var string $projectPath
    private static $projectPath;

    /**
     * We want to make sure, that we don't accidently work with this class instead of its proxy.
     * With a protected constructor, there are still ways to create one instance, e.g. by subclassing
     * in tests.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName) {
        $this->fileName = $fileName;
        $this->id = $this->getIdByFileName($fileName);
        self::getGoogleClient();
    }

    /**
     * Its a kind of proxy design pattern, only that we force the proxy to be used.
     *
     * @param string $fileName
     * @return GoogleFileProx
     */
    public static function getInstance(string $fileName): GoogleFileProxy
    {
        if (!isset(self::$instance[$fileName])) {
            $gf = new GoogleFile($fileName);
            self::$instance[$fileName] = new GoogleFileProxy($fileName, $gf);
        }

        return self::$instance[$fileName];
    }

    /**
     * No type hint, since PHP 7.0 does not allow nullable return type hints.
     *
     * @param string $fileName
     * @return string $fileId
     */
    public static function getIdByFileName(string $fileName)
    {
        $ids = [
            "1IYOS5R9KZ0dK49ZMJHCqmbFv1QqYlBxE" => "todos_history.yml",
            "1uERUVkdXvB6AArpUoXXpiAFB_7wL9bSJ" => "todos.yml",
        ];
        if ($id = array_search($fileName, $ids)) {
            return $id;
        }
        // throw new \Exception("ID für file {$fileName} unbekannt");
        return array_search($fileName, self::listFileNames());
    }

    /**
     * @return bool - Wether the file is found in Google Drive.
     */
    public function exists(): bool
    {
        return !!$this->id;
    }

    public function getGoogleClient()
    {
        if (!self::$service) {
            self::$service = GoogleClient::getInstance()->getClient();
        }

        return self::$service;
    }

    public static function setService(Google_Client $service)
    {
        self::$service = $service;
    }

    /**
     * Siehe https://developers.google.com/drive/v3/web/search-parameters
     * @return [Google_Service_Drive_DriveFile]
     */
    public static function listFiles()
    {
        $optParams = array(
            // 'fields' => 'nextPageToken, files(id, name)'
            // 'parents' => 'todos',
            // 'spaces' => 'todos',
            // 'spaces' => 'appDataFolder',
        );

        $results = self::getGoogleClient()->files->listFiles($optParams);

        return $results;
    }

    /**
     * File-IDs as keys, name as value.
     *
     * Search would be faster if keys and values where swapped,
     * but since we only have a few of them, this should not matter.
     *
     * @return [string]
     */
    public static function listFileNames(): array
    {
        $files = self::listFiles();

        $return = [];
        foreach ($files as $file) {
            $return[$file->getId()] = $file->getName();
        }

        return $return;
    }

    /**
     * @param string $content
     * @return string The file's id.
     */
    public function create(string $content = ''): string
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $this->fileName,
            'parents' => ['todos'],
        ]);

        $file = self::$service->files->create($fileMetadata, [
            'data'       => $content,
            'mimeType'   => 'application/x-yaml',
            'uploadType' => 'multipart',
            'fields'     => 'id',
        ]);

        $this->id = $file->id;
        return $file->id;
    }

    /**
     * @return void - Type hint only in PHP 7.1. available
     */
    public static function deleteAllFiles()
    {
        $fileIds = array_keys(self::listFileNames());
        
        foreach ($fileIds as $fileId) {
            self::deleteByFileId($fileId);
        }
    }

    /**
     * @param string $fileId
     * @return void
     */
    public static function deleteByFileId(string $fileId)
    {
        self::getGoogleClient()->files->delete($fileId);
    }

    /**
     * @return void
     */
    public function delete()
    {
        if (!$this->id) {
            throw new \Exception('File is not created yet');
        }

        self::deleteByFileId($this->id);
    }

    /**
     * @param string $fileId
     * @return Google_Service_Drive_DriveFile
     */
    public static function getFile(string $fileId): Google_Service_Drive_DriveFile
    {
        return $service->files->get($fileId, ['alt' => 'media']);
    }

    public function getContent(): string
    {
        if (!$this->id) {
            throw new \Exception("File mit Namen {$this->fileName} nicht gefunden.");
        }

        if (!$this->content) {
            $response = self::$service->files->get($this->id, array(
                'alt' => 'media'));
            $this->content = $response->getBody()->getContents();
        }

        return $this->content;
    }

    /**
     * @param string $newContent
     * @param string $mimeType
     *
     * @return GoogleFile
     */
    public function updateContent(string $newContent, string $mimeType = 'text/json'): GoogleFile
    {
        if (!$this->id) {
            throw new \Exception("File mit Namen {$this->fileName} nicht gefunden.");
        }

        $updateFile = new Google_Service_Drive_DriveFile();
        $file = self::$service->files->update($this->id, $updateFile, [
            'data' => $newContent,
            'mimeType'   => $mimeType,
            'uploadType' => 'multipart',
        ]);

        $this->content = $newContent;
        return $this;
    }

    /**
     * @param array $array
     * return $string - $newContent
     */
    public function updateYaml(array $array): string
    {
        $dump = Yaml::dump($array);
        $this->updateContent($dump, 'text/x-yaml');
        return $dump;
    }

    public function appendYaml(array $newContentArray): string
    {
        $contentArray = $this->parseYaml();
        $contentArray = array_merge($contentArray, $newContentArray);

        return $this->updateYaml($contentArray);
    }

    /**
     * We expect yaml-files to always represent arrays.
     *
     * @return array
     */
    public function parseYaml(): array
    {
        return Yaml::parse($this->getContent());
    }

    /**
     * @return string
     */
    public static function getProjectPath(): string
    {
        if (!self::$projectPath) {
            self::$projectPath = __DIR__;

            while (!in_array('src', scandir(self::$projectPath))) {
                self::$projectPath = dirname(self::$projectPath);

                if (self::$projectPath == '/') {
                    throw new \Exception('Unable to find project path. No src-Folder found.');
                }
            }
        }

        return self::$projectPath;
    }

    /**
     * @return string
     */
    public static function getFileCache(): string
    {
        if (!self::$fileCache) {
            self::$fileCache = getenv('GOOGLE_CLIENT_FILE_CACHE') ? : self::getProjectPath() . '/google-client-file-cache';
        }

        return self::$fileCache;
    }

    /**
     * @return $this
     */
    public function copyToFileCache(): GoogleFile
    {
        $cacheDir = self::getFileCache();

        if (!file_exists($cacheDir)) {
            mkdir(self::getFileCache(), 0755, true);
        }

        file_put_contents(
            self::getFileCache() . '/' . $this->fileName,
            $this->getContent()
        );

        return $this;
    }

    public static function listFilesOfFolderId($folderId)
    {
        $optParams = ['q' => "'{$folderId}' in parents"];
        $results = self::getGoogleClient()->files->listFiles($optParams);

        $return = [];
        foreach ($results as $file) {
            $return[$file->getId()] = $file->getName();
        }

        return $return;
    }
}

// GoogleFile::deleteAllFiles();
// var_dump(GoogleFile::listFileNames());
// var_dump(GoogleFile::listFilesOfFolderId('1j3WPjpo2nPekddpi4rWRwC0GhtmdRIrO'));

// $file = new GoogleFile('todos.yml');
// $file->create("-\n    dateString: '21.03.2018'\n    normalizedDateString: '2018-03-21 23:59:59'\n    text: Mi vorbei\n    displayTime: 2d\n    normalizedDisplayTime: P2D");

// $file = new GoogleFile('todos.yml');
// echo $file->getContent() . "\n";
// var_dump($file->parseYaml());

// $file = new GoogleFile('todos.yml');
// $file->updateContent("-\n  id: 1\n  text: Immer noch YML-Parser einbauen");

// $file = new GoogleFile('todos.yml');
// echo $file->getContent() . "\n";

// $file = new GoogleFile('todos.yml');
// $file->updateYaml([['id' => 1, 'todo' => 'blabla']]);
// $orig = $file->parseYaml();
// var_dump($orig);
