<?php

namespace APPointer\tests\Lib;

use PHPUnit\Framework\TestCase;
use APPointer\Lib\MediaCenter;
use APPointer\tests\Entity\Fixtures;
use APPointer\tests\Lib\BashSpy;

class MediaCenterTest extends TestCase
{
    // @var MediaCenter
    private $mediaCenter;

    public function setUp()
    {
        $this->bash = new BashSpy();
        $this->mediaCenter = new MediaCenter($this->bash);
    }

    public function testIsMounted()
    {
        $command = "stat -f -c '%T' ~/Mediacenter";
        $this->bash->addMapping($command, 'fuseblk');
        $this->assertTrue($this->mediaCenter->isMounted());
        $this->assertSame([$command], $this->bash->getCalls());

        $this->bash->reset();
        $this->bash->addMapping($command, 'ext2/ext3');
        $this->assertFalse($this->mediaCenter->isMounted());
        $this->assertSame([$command], $this->bash->getCalls());
    }

    public function testMountSuccessfully()
    {
        // First, its not mounted.
        $command = "stat -f -c '%T' ~/Mediacenter";
        $this->bash->addSingleMapping($command, 'ext2/ext3');
        $this->assertFalse($this->mediaCenter->isMounted());
        $this->assertSame([$command], $this->bash->getCalls());

        // We do the mounting.
        $this->bash->reset();
        $command = "stat -f -c '%T' ~/Mediacenter";
        $this->bash->addSingleMapping($command, 'ext2/ext3');
        $this->bash->addSingleMapping($command, 'fuseblk');
        $this->assertTrue($this->mediaCenter->mount());

        $calls = $this->bash->getCalls();
        // $this->assertSame([$command, 'mount ~/Mediacenter', $command], $this->bash->getCalls());
        $this->assertSame($command, $calls[0]);
        $this->assertSame($command, $calls[2]);
        $this->assertRegExp('@mount /home/.*/Mediacenter@', $calls[1]);
    }

    public function testMountUnsuccessfully()
    {
        // First, its not mounted.
        $command = "stat -f -c '%T' ~/Mediacenter";
        $this->bash->addSingleMapping($command, 'ext2/ext3');
        $this->assertFalse($this->mediaCenter->isMounted());
        $this->assertSame([$command], $this->bash->getCalls());

        // We try to do the mounting, but it fails.
        $this->bash->reset();
        $command = "stat -f -c '%T' ~/Mediacenter";
        $this->bash->addSingleMapping($command, 'ext2/ext3');
        $this->bash->addSingleMapping($command, 'ext2/ext3');
        $this->assertFalse($this->mediaCenter->mount());
        $calls = $this->bash->getCalls();
        $this->assertSame($command, $calls[0]);
        $this->assertSame($command, $calls[2]);
        $this->assertRegExp('@mount /home/.*/Mediacenter@', $calls[1]);
    }
}
