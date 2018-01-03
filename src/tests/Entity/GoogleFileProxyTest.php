<?php

namespace SniTodos\tests\Entity;

use PHPUnit\Framework\TestCase;
use SniTodos\Entity\GoogleFileProxy;
use SniTodos\Entity\GoogleFile;
use SniTodos\Lib\Filesystem;

class GoogleFileProxyTest extends TestCase
{
    public function setUp()
    {
        $this->googleFile = $this->getGoogleFile();
    }

    private function getGoogleFile()
    {
        $file = $this
            ->getMockBuilder('SniTodos\Entity\GoogleFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getContent')
            ->willReturn('Some content');

        return $file;
    }

    public function testGetContentUncached()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');
        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with(
                $this->stringContains('/google-client-file-cache/test.yml'),
                'Some content'
            );
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $content = $this->googleFile->getContent();
        $this->assertSame('Some content', $content);

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);

        $content = $file->getContent();
        $this->assertSame('Some content', $content);
    }

    public function testGetContentCached()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');
        $filesystem
            ->expects($this->never())
            ->method('dumpFile');
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $filesystem
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('Some content');

        $content = $this->googleFile->getContent();
        $this->assertSame('Some content', $content);

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);

        $content = $file->getContent();
        $this->assertSame('Some content', $content);
    }

    public function testSetContent()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');

        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with(
                $this->stringContains('/google-client-file-cache/test.yml'),
                'Some other content'
            );

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);
        $file->setContent('Some other content');
    }

    public function testClearCache()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');

        $filesystem
            ->expects($spy = $this->once())
            ->method('remove');

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);
        $file->clearCache();

        // TODO SNI
        $call = $spy->getInvocations()[0];
        $params = $call->getParameters();
        $this->assertEquals(1, count($params));
        $this->assertEquals(1, count($params[0]));

        $this->assertRegExp('@.+/google-client-file-cache/test.yml$@', $params[0][0]);
    }

    public function testUploadForExistingFile()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');

        $filesystem
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('Upload for existing file');

        $this->googleFile
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->googleFile
            ->expects($this->once())
            ->method('updateContent');

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);
        $file->upload();
    }

    public function testUploadForNewFile()
    {
        $filesystem = $this
            ->createMock('SniTodos\Lib\Filesystem');

        $filesystem
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('Upload for existing file');

        $this->googleFile
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->googleFile
            ->expects($this->once())
            ->method('create');

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);
        $file->upload();
    }
}
