<?php

namespace SniTodos\tests\Entity;

use PHPUnit\Framework\TestCase;
use Google_Service_Drive_DriveFile;
use SniTodos\Entity\GoogleFile;
use SniTodos\Entity\GoogleFileProxy;

class PublicGoogleFile extends GoogleFile
{
    public function __construct(string $fileName) {
        parent::__construct($fileName);
    }
}


class GoogleFileTest extends TestCase
{
    public function setUp()
    {
        $this->googleClient = $this
            ->createMock('Google_Client');

        $files = $this
            ->getMockBuilder('Google_Service_Drive_Resource_Files')
            ->disableOriginalConstructor()
            ->getMock();

        $todosFile = new Google_Service_Drive_DriveFile();
        $todosFile->setId('todos_id');
        $todosFile->setName('ToDos.yml');

        $oldTodosFile = new Google_Service_Drive_DriveFile();
        $oldTodosFile->setId('old_todos_id');
        $oldTodosFile->setName('Old_ToDos.yml');

        $files
            ->method('listFiles')
            ->willReturn([$todosFile, $oldTodosFile]);

        $this->googleClient->files = $files;
        GoogleFile::setService($this->googleClient);
    }

    public function testGetIdByFileName()
    {
        $this->assertSame('todos_id', GoogleFile::getIdByFileName('ToDos.yml'));
        $this->assertSame('old_todos_id', GoogleFile::getIdByFileName('Old_ToDos.yml'));
        $this->assertFalse(GoogleFile::getIdByFileName('Gibt es nicht'));
    }

    public function testGetGoogleClient()
    {
        $file = new PublicGoogleFile('ToDos.yml');
        $this->assertSame($this->googleClient, $file->getGoogleClient());
    }

    public function testListFileNames()
    {
        $result = GoogleFile::listFileNames();

        $this->assertTrue(is_array($result));

        $this->assertArrayHasKey('todos_id', $result);
        $this->assertArrayHasKey('old_todos_id', $result);
        $this->assertCount(2, $result);

        $this->assertSame('ToDos.yml', $result['todos_id']);
        $this->assertSame('Old_ToDos.yml', $result['old_todos_id']);
    }

    public function testCreate()
    {
        $file = new PublicGoogleFile('ToDos.yml');

        $driveFile = new Google_Service_Drive_DriveFile();
        $driveFile->setId('new_drive_files_id');

        $this->googleClient
            ->files
            ->expects($this->once())
            ->method('create')
            ->willReturn($driveFile);

        $result = $file->create('some content');
        $this->assertSame('new_drive_files_id', $result);
    }

    public function testDeleteAllFiles()
    {
        $this->googleClient->files
            ->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive(
                [$this->equalTo('todos_id')],
                [$this->equalTo('old_todos_id')]
            );

        GoogleFile::deleteAllFiles();
    }

    public function testDelete()
    {
        $file = new PublicGoogleFile('ToDos.yml');
        $this->googleClient->files
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('todos_id'));

        $file->delete();
    }

    /**
     * Soll später benutzt werden, um das File zu cachen.
     */
    public function testGetProjectPath()
    {
        $path = GoogleFile::getProjectPath();
        $this->assertRegExp('@^/\w+@', $path);
    }

    protected function getResponse($content)
    {
        $response = $this->createMock('GuzzleHttp\Psr7\Response');

        $body = $this->createMock('GuzzleHttp\Psr7\Stream');
        $body
            ->method('getContents')
            ->willReturn($content);

        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    public function testGetContent()
    {
        $file = new PublicGoogleFile('ToDos.yml');

        $this->googleClient->files
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('todos_id'), ['alt' => 'media'])
            ->willReturn($this->getResponse('Content'));

        $content = $file->getContent();
        $this->assertSame('Content', $content);
    }

    public function testUpdateContent()
    {
        $file = new PublicGoogleFile('ToDos.yml');

        $this->googleClient->files
            ->expects($this->once())
            ->method('update');

        $file->updateContent('New Content');
        $this->assertSame('New Content', $file->getContent());
    }

    public function testUpdateYaml()
    {
        $file = new PublicGoogleFile('ToDos.yml');

        $this->googleClient->files
            ->expects($spy = $this->once())
            ->method('update');

        $file->updateYaml(['foo' => 'bar']);

        $call = ($spy->getInvocations())[0];
        $parameters = $call->getParameters();

        $this->assertEquals('todos_id', $parameters[0]);
        $this->assertInstanceOf(Google_Service_Drive_DriveFile::class, $parameters[1]);
        $this->assertRegExp('@^\s*foo:\s+bar\s*$@', $parameters[2]['data']);
        $this->assertSame('text/x-yaml', $parameters[2]['mimeType']);
        $this->assertSame('multipart', $parameters[2]['uploadType']);
    }

    public function testParseYaml()
    {
        $file = new PublicGoogleFile('ToDos.yml');

        $content = '{"foo": ["Null", "Eins"]}';

        $this->googleClient->files
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('todos_id'), ['alt' => 'media'])
            ->willReturn($this->getResponse($content));

        $content = $file->parseYaml();
        $this->assertArrayHasKey('foo', $content);
        $this->assertSame(['Null', 'Eins'], $content['foo']);
    }

    /**
     * @return string $fileCache - Path to where the todos.yml etc. will be stored during the session.
     */
    public function testGetFileCache()
    {
        $this->assertRegExp('@/.*/google-client-file-cache@', GoogleFile::getFileCache());
    }

    public function testExists()
    {
        $file1 = new PublicGoogleFile('ToDos.yml');
        $this->assertTrue($file1->exists());
        
        $file2 = new PublicGoogleFile('wrong.yml');
        $this->assertFalse($file2->exists());
    }

    public function testGetInstance()
    {
        $first  = GoogleFile::getInstance('first.txt');
        $second = GoogleFile::getInstance('second.txt');
        $first2 = GoogleFile::getInstance('first.txt');

        $this->assertSame(GoogleFileProxy::Class, get_class($first));

        $this->assertNotSame($first, $second);
        $this->assertSame($first, $first2);
    }
}
