<?php

namespace SniTodos\tests\Lib\AtJobs;

use PHPUnit\Framework\TestCase;
use SniTodos\Lib\AtJobs\AtJobsManager;
use SniTodos\Entity\DzenMessage;

class AtJobsManagerTest extends TestCase
{

    private function getManager()
    {
        $fs = $this->createMock('SniTodos\Lib\Filesystem');
        $fs->expects($this->getContentSpy = $this->any())
            ->method('getContent')
            ->willReturn('{"hash1":1,"hash2":2}');
        $fs->expects($this->dumpFileSpy = $this->any())
            ->method('dumpFile');

        $installer = $this->getMockBuilder('SniTodos\Lib\AtJobs\Installer')
            ->disableOriginalConstructor()
            ->setMethods(['install', 'remove', 'getAtIds'])
            ->getMock();
        $installer->expects($this->installSpy = $this->any())
            ->method('install')
            ->willReturn(3);
        $installer->expects($this->removeSpy = $this->any())
            ->method('remove');
        $installer->expects($this->getAtIdsSpy = $this->any())
            ->method('getAtIds')
            ->willReturn([2, 3]);

        $manager = new AtJobsManager($fs, $installer);
        return $manager;
    }

    public function testGetInstalledAtJobs()
    {
        $manager = $this->getManager();

        $this->assertSame([
            'hash1' => 1,
            'hash2' => 2,
        ], $manager->getInstalledAtJobs());
    }

    public function testInstallDzenMessage()
    {
        $message = $this->createMock(DzenMessage::class);
        $message
            ->expects($this->once())
            ->method('getHash')
            ->willReturn('hash3');

        $manager = $this->getManager();
        $manager->installDzenMessage($message);
        $call = $this->dumpFileSpy->getInvocations()[0];
        $this->assertContains('/at_jobs.csv', $call->getParameters()[0]);
        $this->assertSame('{"hash1":1,"hash2":2,"hash3":3}', $call->getParameters()[1]);
    }

    public function testRemoveJob_wrongId()
    {
        $manager = $this->getManager();
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Kein AtJob mit id 3');
        $manager->removeJob(3);
    }

    public function testRemoveJob_correctId()
    {
        $manager = $this->getManager();
        $this->assertTrue($manager->removeJob(2));

        $call = $this->removeSpy->getInvocations()[0];
        $this->assertSame([2], $call->getParameters());

        $call = $this->dumpFileSpy->getInvocations()[0];
        $parameters = $call->getParameters();
        $this->assertContains('/at_jobs.csv', $parameters[0]);
        $this->assertEquals('{"hash1":1}', $parameters[1]);
    }

    public function testCleanup()
    {
        $manager = $this->getManager();

        // We mocked filesystem, so that the manager knows about jobs for id 1 and 2.
        // We mocked the installer to return only the ids 2 and 3 for 'at -l'.
        // So the manager believes that 3 is an external job, and 1 was already
        // dropped by at, so the manager can also forget about it.
        $manager->cleanup();

        $call = $this->dumpFileSpy->getInvocations()[0];
        $parameters = $call->getParameters();
        $this->assertContains('/at_jobs.csv', $parameters[0]);
        $this->assertEquals('{"hash2":2}', $parameters[1]);
    }
}
