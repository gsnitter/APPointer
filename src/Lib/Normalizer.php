<?php

namespace SniTodos\Lib;

use SniTodos\Parser as Parser;
use SniTodos\Entity\Todo;
use SniTodos\Parser\ParserBase;

class Normalizer
{
    // @var GoogleClient
    private static $instance;

    // @var ParserInterfacce[]
    private $parsers;

    /**
     * Singelton
     * @return Normalizer
     */
    public static function getInstance(): Normalizer
    {
        if (!self::$instance) {
            self::$instance = new Normalizer();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->createParsers();
    }

    /**
     * @return array
     */
    public function getPropertyParsers(): array
    {
        return [
            'dateString' => $this->parsers['DateParser'],
            'displayTime' => $this->parsers['DisplayTimeParser'],
            'alarmTimes' => $this->parsers['AlarmTimesParser'],
        ];
    }


    public function getParserClasses(): array
    {
        return array_map(function($parserPath) {
            return filename($parserPath);
        }, $parserPaths);
    }

    private function createParsers(): array
    {
        $dir = dirname(__DIR__) . '/Parser/';
        $parserFiles = glob($dir . '*Parser.php');

        foreach ($parserFiles as $parserFile) {
            preg_match('@(\w+Parser).php@', $parserFile, $matches);
            if (!$matches) {
                throw new \Exception("Cannot parse ClassName from path {$parserFile}");
            } else {
                $className = $matches[1];
                $classString = 'SniTodos\\Parser\\' . $className;
                $this->parsers[$className] = new $classString();
            }
        }

        return $this->parsers;
    }

    /**
     * Here we normalize the properties, e.g. for AlarmTimes we do
     *     $todo->setNormalizedAlarmTimes($alarmTimesParser->normalize($todo->getAlarmTimes()));
     * @param Todo $todo
     * @return $this
     */
    public function normalize(Todo $todo): Normalizer
    {
        foreach ($this->getPropertyParsers() as $property => $parser) {
            $getter = 'get' . ucfirst($property);
            $setter = 'setNormalized' . ucfirst($property);

            $this->passNeededNormalizedDataToParser($todo, $parser);
            $todo->$setter($parser->normalize($todo->$getter()));
        }

        return $this;
    }

    private function passNeededNormalizedDataToParser(Todo $todo, ParserBase $parser): Normalizer
    {
        $neededs = $parser->getNeededNormalizedValues();

        foreach ($neededs as $needed) {
            $getter = 'getNormalized' . ucfirst($needed);
            $setter = preg_replace('@^g@', 's', $getter);

            $parser->$setter($todo->$getter());
        }

        return $this;
    }
}
