<?php

namespace APPointer\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use APPointer\Entity\DzenMessage;

/**
 * Should accept a single string like '20:00' or '2h' 
 * or arrays of string like ['22:00', '22:30 red']
 * and of course full notation and transform it to e.g.
 * [['time': '2017-01-13 22:00', type: DzenMessage::NORMAL], ['time': '2017-01-13 22:30', type: DzenMessage::BAD_NEWS]]
 */
class AlarmTimesParser extends ParserBase {

    /** @var string normalizedDateString */
    private $normalizedDateString;

    public function normalize($alarmTimes)
    {
        if (!$alarmTimes) {
            return [];
        }
        if (is_string($alarmTimes)) {
            $alarmTimes = [$alarmTimes];
        }
        foreach ($alarmTimes as &$alarmTime) {
            $this->addKeys($alarmTime);
            $this->addDate($alarmTime);
            $this->addType($alarmTime);
        }

        return $alarmTimes;
    }

    private function addKeys(&$alarmTime)
    {
        if (is_string($alarmTime)) {
            $words = preg_split('@\s+@', $alarmTime);
            $type = DzenMessage::NORMAL;
            $dateString = substr($this->normalizedDateString, 0, 10);

            foreach ($words as $word) {
                if (preg_match('@^20\d{2}-\d{2}-\d{2}$@', $word)) {
                    $dateString = $word;
                }
                if (preg_match('@^\d{2}:\d{2}$@', $word, $matches)) {
                    $timeString = $matches[0];
                }
                if ($result = DzenMessage::stringToType($word)) {
                    $type = $result;
                }
            }
            if (!isset($timeString)) {
                throw new \Exception("No parseable time string found in string '$alarmTime'.");
            }
            $alarmTime = [
                'time' => $dateString . ' ' . $timeString,
                'type' => $type,
            ];
        }
    }

    private function addType(&$alarmTime)
    {
        // Light code smell, we could have extracted the types from DzenMessage,
        // so we could easier exchange the message-class.
        $alarmTime['type'] = $alarmTime['type'] ?? DzenMessage::NORMAL;
    }

    private function addDate(&$alarmTime)
    {
        $timeString = trim($alarmTime['time']);

        if (!preg_match('@20\d{2}-\d{2}-\d{2}@', $timeString)) {
            $timeString = substr($this->normalizedDateString, 0, 10) . ' ' . $timeString;
        }

        $alarmTime['time'] = $timeString;
    }

    public function validate($value, ExecutionContextInterface $context, $payload)
    {
        if (!$value) {
            return;
        }

        try {
            $normalizedString = $this->normalize($value);
        } catch (\Exception $e) {
            $string = print_r($value, true);
            $context->buildViolation("AlarmTimesParser: Unable to parse {$string}")
                ->atPath('alarmTimes')
                ->addViolation();
        }
    }

    protected function getMemberName()
    {
        $refClass = new ReflectionClass(get_class($this));
        return lcfirst($refClass->getShortName());
    }

    public function getNeededNormalizedValues(): array
    {
        return ['dateString'];
    }

    public function setNormalizedDateString(string $normalizedDateString): AlarmTimesParser
    {
        $this->normalizedDateString = $normalizedDateString;
        return $this;
    }
}
