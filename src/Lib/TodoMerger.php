<?php

namespace APPointer\Lib;

use APPointer\Lib\DI;
use APPointer\Lib\MediaCenter;
use APPointer\Lib\Filesystem;

/**
 * @class TodoMerger
 *
 * Merges two TodoFiles
 */
class TodoMerger {

    /** @var string */
    private $localPath;

    /** @var string */
    private $foreignPath;

    /** @var Filesystem */
    private $fs;

    /**
     * If two entries have same normalizedCreatedAt in both files,
     * we take the one with newer normalizedUpdatedAt.
     */
    const CREATED_AT_FIELD='normalizedCreatedAt';
    const UPDATED_AT_FIELD='normalizedUpdatedAt';

    public function __construct(Filesystem $fs)
    {
        $this->localPath = DI::getLocalPath();
        $this->foreignPath = MediaCenter::getDriveLocation() . '/todos.yml';
        $this->fs = $fs;
    }

    public function merge()
    {
        $newContent = $this->mergeSourceTarget($this->foreignPath, $this->localPath);
        $this->fs->dumpYaml($this->localPath, $newContent);
    }

    public function remerge()
    {
        $newContent = $this->mergeSourceTarget($this->localPath, $this->foreignPath);
        $this->fs->dumpYaml($this->foreignPath, $newContent);
    }

    private function mergeSourceTarget(string $sourcePath, string $targetPath): array
    {
        $sourceArray =  $this->useNormalizedCreatedAtAsKey($this->fs->loadYaml($sourcePath));
        $targetArray =  $this->useNormalizedCreatedAtAsKey($this->fs->loadYaml($targetPath));

        $sourceKeys = array_keys($sourceArray);
        foreach ($sourceKeys as $key) {
            if (!isset($targetArray[$key])) {
                continue;
            }

            if (!isset($targetArray[$key][self::UPDATED_AT_FIELD])) {
                unset($targetArray[$key]);
            } else {
                if (isset($sourceArray[$key][self::UPDATED_AT_FIELD])) {
                    $sourceUpdatedAt = $sourceArray[$key][self::UPDATED_AT_FIELD];
                    $targetUpdatedAt = $targetArray[$key][self::UPDATED_AT_FIELD];
                    if ($sourceUpdatedAt > $targetUpdatedAt) {
                        unset($targetArray[$key]);
                    }
                }
            }
        }

        $result = array_merge($sourceArray, $targetArray);
        ksort($result);
        return $result;
    }

    private function useNormalizedCreatedAtAsKey(array $todosArray)
    {
        foreach ($todosArray as &$todoArray) {
            if (!isset($todoArray[self::CREATED_AT_FIELD])) {
                $dt = new \DateTime('01.01.2000');
                $dt
                    ->add(new \DateInterval(('PT' . rand(0, 100000) . 'S')));
                $todoArray[self::CREATED_AT_FIELD] = $dt->format('Y-m-d H:i:s');
            }
        }

        $keys = array_map(function(&$element) {
            return $element[self::CREATED_AT_FIELD];
        }, $todosArray);

        return array_combine($keys, $todosArray);
    }
}
