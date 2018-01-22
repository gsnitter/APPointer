<?php

namespace SniTodos\Lib\AtJobs;
use SniTodos\Lib\DI;
use SniTodos\Lib\Filesystem;
use SniTodos\Lib\AtJobs\Installer;
use SniTodos\Entity\DzenMessage;

class AtJobsManager
{
    // @var string
    private $csvPath;
    
    // @var Installer
    private $installer;

    public function __construct(Filesystem $fs, Installer $installer)
    {
        $this->fs = $fs;
        $this->installer = $installer;
        $this->csvPath = DI::getStoragePath() . '/at_jobs.csv';
    }

    public function getInstalledAtJobs()
    {
        return json_decode($this->fs->getContent($this->csvPath), true);
    }

    public function installDzenMessage(DzenMessage $message): int
    {
        $id = $this->installer->install($message);
        if (!$id) {
            return 0;
        }

        $hashes = $this->getInstalledAtJobs();
        $hashes[$message->getHash()] = $id;
        $this->dumpFile($hashes);
        return $id;
    }

    private function dumpFile(array $hashes)
    {
        $this->fs->dumpFile($this->csvPath, json_encode($hashes));
    }

    public function removeJob(int $jobId): bool
    {
        $jobsArray = $this->getInstalledAtJobs();
        $hash = array_search($jobId, $jobsArray);

        if (!$hash) {
            throw new \OutOfBoundsException("Kein AtJob mit id {$jobId} in {$this->csvPath} gefunden");
        }

        unset($jobsArray[$hash]);
        $this->dumpFile($jobsArray);
        $this->installer->remove($jobId);

        return true;
    }

    /**
     * When at completes a job, we don't need to remember its hash any more.
     */
    public function cleanup()
    {
        $jobsArray = $this->getInstalledAtJobs();
        $atIds = $this->installer->getAtIds();

        $jobsArray = array_filter($jobsArray, function($id) use ($atIds) {
            return in_array($id, $atIds);
        });

        $this->dumpFile($jobsArray);
    }
}
