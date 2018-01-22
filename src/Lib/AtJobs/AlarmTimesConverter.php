<?php

namespace SniTodos\Lib\AtJobs;

use SniTodos\Entity\DzenMessage;

class AlarmTimesConverter
{

    /**
     * @param array $alarmTimesArray
     * @return DzenMessage[]
     */
    public function createDzenMessages(array $alarmTimesArray): array
    {
        return array_map(function($alarmTimeArray) {
            return $this->createDzenMessage($alarmTimeArray);
        }, $alarmTimesArray);
    }

    /**
     * Converts an alarmTime-Array in a DzenMessage
     * ['time' =>  '2018-01-09 22:00', 'type' => 1, 'message' => 'eins']
     */
    public function createDzenMessage(array $alarmTime): DzenMessage
    {
        $message = new DzenMessage($alarmTime['message'], $alarmTime['time']);
        $message->setType($alarmTime['type']);
        return $message;
    }
}
