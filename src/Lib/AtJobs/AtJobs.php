<?php

namespace SniTodos\Lib\AtJobs;

use SniTodos\Entity\Todo;
use SniTodos\Lib\TodosFileParser;

class AtJobs
{
    private $todosFileParser;
    private $atJobsManager;
    private $alarmTimesConverter;

    public function __construct(
        TodosFileParser $todosFileParser,
        AtJobsManager $atJobsManager,
        AlarmTimesConverter $alarmTimesConverter
    ) {
        $this->todosFileParser = $todosFileParser;
        $this->atJobsManager = $atJobsManager;
        $this->alarmTimesConverter = $alarmTimesConverter;
    }

    public function create()
    {
        $alarmTimes = $this->todosFileParser->getAlarmTimes();
        $installedAtJobs = $this->atJobsManager->getInstalledAtJobs();
        $dzenMessages = $this->alarmTimesConverter->createDzenMessages($alarmTimes);

        // Install at-jobs for new or updated alarm times
        foreach ($dzenMessages as $dzenMessage) {
            if (!in_array($dzenMessage->getHash(), array_keys($installedAtJobs))) {
                $this->atJobsManager->installDzenMessage($dzenMessage);
            }
        }

        $dzenMessageHashes = array_map(function($dzenMessage) {
            return $dzenMessage->getHash();
        }, $dzenMessages);

        // Remove outdated or changed at jobs, installed by old alarm times
        foreach ($installedAtJobs as $atJobHash => $atJobId) {
            if (!in_array($atJobHash, $dzenMessageHashes)) {
                $this->atJobsManager->removeJob($atJobId);
            }
        }

        // We don't need to remember hashes of jobs, that are already gone.
        $this->atJobsManager->cleanup();
    }
}
