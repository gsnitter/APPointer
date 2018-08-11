<?php

namespace APPointer\tests\Lib;

use PHPUnit\Framework\TestCase;
use APPointer\tests\Lib\FilesystemTest;
use APPointer\tests\Entity\Fixtures;
use APPointer\Lib\TodoMerger;
use APPointer\Lib\MediaCenter;
use APPointer\Lib\DI;

class TodoMergerTest extends TestCase
{
    public function setUp()
    {
        $this->fs = new FilesystemTest();
        $this->todoMerger = new TodoMerger($this->fs);

        $this->localPath = getenv('APPOINT_LOCAL_FILE') ? : DI::getProjectPath() . '/todos.yml';
        $this->foreignPath = MediaCenter::getDriveLocation() . '/todos.yml';
    }

    public function testMerge()
    {
        $foreignContent = Fixtures::getForeignTodoFileContent();
        $localContent = Fixtures::getLocalTodoFileContent();

        $this->fs->dumpFile($this->foreignPath, $foreignContent);
        $this->fs->dumpFile($this->localPath, $localContent);

        $this->todoMerger->merge();

        $localTodosArray = $this->fs->loadYaml($this->localPath);
        $this->assertEquals(3, count($localTodosArray));
        $this->assertArrayHasKey('2018-03-01 12:00:00', $localTodosArray);
        $duplicatedElement = $localTodosArray['2018-03-01 12:00:00'];
        $this->assertEquals('2018-03-01 14:00:00', $duplicatedElement['normalizedUpdatedAt'], print_r($duplicatedElement, true));
    }
}
