<?php

namespace SniTodos\tests\Entity;

use PHPUnit\Framework\TestCase;
use SniTodos\Entity\GoogleFileProxy;
use SniTodos\Entity\GoogleFile;

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
            ->createMock('Symfony\Component\Filesystem\Filesystem');
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
            ->createMock('Symfony\Component\Filesystem\Filesystem');
        $filesystem
            ->expects($this->never())
            ->method('dumpFile');
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $content = $this->googleFile->getContent();
        $this->assertSame('Some content', $content);

        $file = new GoogleFileProxy('test.yml', $this->googleFile);
        $file->setFileystem($filesystem);

        // Unfortunately, the filesystem component has no wrapper for file_get_contents.
        // Not wanting to subclass it, we need to catch the warning for testing.
        try {
            $content = $file->getContent();
            $this->assertSame('Some content', $content);
        } catch (\Exception $e) {
            $this->assertContains('No such file', $e->getMessage());
        }
    }

    public function testSetContent()
    {
        $filesystem = $this
            ->createMock('Symfony\Component\Filesystem\Filesystem');

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
            ->createMock('Symfony\Component\Filesystem\Filesystem');

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
}
