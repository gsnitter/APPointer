<?php

namespace APPointer\tests\Lib\AtJobs;

use PHPUnit\Framework\TestCase;
use APPointer\Lib\AtJobs\AtJobs;
use APPointer\tests\Entity\Fixtures;
use APPointer\Entity\Todo;
use APPointer\Lib\DI;
use APPointer\Entity\DzenMessage;

class AtJobsTest extends TestCase
{
    // @var AtJobsCreator
    private $creator;

    // Before implenting this class, we did not use the symfony dependency injection,
    // so we test the setup here.
    public function testContainerSetup()
    {
        $container = DI::getContainer();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);

        $facade = $container->get(AtJobs::class);
        $this->assertInstanceOf('APPointer\Lib\AtJobs\AtJobs', $facade);
    }

    public function testCreate()
    {
        $todosFileParser = $this->createMock('APPointer\Lib\TodosFileParser');
        $todosFileParser->expects($this->once())
            ->method('getAlarmTimes')
            ->willReturn($this->getAlarmTimes());

        $atJobsManager = $this->getMockBuilder('APPointer\Lib\AtJobs\AtJobsManager')
            ->disableOriginalConstructor()
            ->setMethods(['getInstalledAtJobs', 'installDzenMessage', 'removeJob', 'cleanup'])
            ->getMock();

        $atJobsManager->expects($this->any())
            ->method('getInstalledAtJobs')
            ->willReturn(['hash1' => 5, 'hash2' => 7, '932978e060613f62ca262975a16f6aa0a973b028' => 8]);

        $alarmTimesConverter = $this->getMockBuilder('APPointer\Lib\AtJobs\AlarmTimesConverter')
            ->setMethods(['createDzenMessages', 'removeJob'])
            ->getMock();

        $alarmTimesConverter->expects($this->once())
            ->method('createDzenMessages')
            ->willReturn($this->getDzenMessages());

        $atJobsManager->expects($spy1 = $this->exactly(2))
            ->method('installDzenMessage')
            ->willReturn(true);

        $atJobsManager->expects($spy2 = $this->exactly(2))
            ->method('removeJob')
            ->willReturn(true);

        $atJobsManager->expects($this->once())
            ->method('cleanup')
            ->willReturn(true);

        $atJobs = new AtJobs($todosFileParser, $atJobsManager, $alarmTimesConverter);
        $atJobs->create();

        $firstDzenMessageToInstall = ($spy1->getInvocations()[0])->getParameters()[0];
        $this->assertInstanceOf(DzenMessage::class, $firstDzenMessageToInstall);
        $this->assertSame('eins', $firstDzenMessageToInstall->getMessage());

        $secondDzenMessageToInstall = ($spy1->getInvocations()[1])->getParameters()[0];
        $this->assertInstanceOf(DzenMessage::class, $secondDzenMessageToInstall);
        $this->assertSame('drei', $secondDzenMessageToInstall->getMessage());

        $firstAtJobToCancel = ($spy2->getInvocations()[0])->getParameters()[0];
        $this->assertSame(5, $firstAtJobToCancel);

        $firstAtJobToCancel = ($spy2->getInvocations()[1])->getParameters()[0];
        $this->assertSame(7, $firstAtJobToCancel);
    }

    private function getDzenMessages()
    {
        return array_map(function($alarmTime) {
            $dzenMessage = new DzenMessage($alarmTime['message'], $alarmTime['time']);
            $dzenMessage
                ->setType($alarmTime['type'])
                ;
            return $dzenMessage;
        }, $this->getAlarmTimes());
    }

    private function getAlarmTimes()
    {
        return [
            ['time' =>  '2018-01-09 22:00', 'type' => 1, 'message' => 'eins'],
            ['time' =>  '2018-01-09 22:00', 'type' => 2, 'message' => 'zwei'],
            ['time' =>  '2018-01-09 22:00', 'type' => 3, 'message' => 'drei'],
        ];
    }
}
