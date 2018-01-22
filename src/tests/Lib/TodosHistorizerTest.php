<?php

namespace SniTodos\tests\Lib;

use PHPUnit\Framework\TestCase;
use SniTodos\Lib\TodosHistorizer;
use SniTodos\Entity\Todo;
use SniTodos\tests\Entity\Fixtures;
use SniTodos\Lib\TodosFileParser;

class TodosHistorizerTest extends TestCase
{
    public function testHistorize()
    {
        $todos = Fixtures::getNormalizedTodos();
        $todosFileParser = $this->createMock(TodosFileParser::class);
        $todosFileParser
            ->expects($this->once())
            ->method('getTodos')
            ->will($this->returnValue($todos));

        $todosSaver = $this->getMockBuilder('SniTodos\Lib\TodosSaver')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $todosSaver
            ->expects($spy = $this->exactly(2))
            ->method('save')
            ->willReturn(true);

        TodosHistorizer::setTime(new \DateTime('2018-01-22 23:00:00'));
        $historizer = new TodosHistorizer($todosFileParser, $todosSaver);
        $historizer->historize();

        $params = ($spy->getInvocations()[0])->getParameters();
        $this->assertSame(2, count($params));
        list($googleFileName, $todosToSave) = $params;
        $this->assertSame('todos.yml', $googleFileName);
        $this->assertGreaterThanOrEqual(2, count($todosToSave));
        $texts = $this->getTodosTexts($todosToSave);
        $this->assertContains('Xmas 18', $texts);
        $this->assertContains('New Year\'s Eve 18', $texts);
        $this->assertNotContains('Xmas 17', $texts);
        $this->assertNotContains('New Year\'s Eve 17', $texts);

        $params = ($spy->getInvocations()[1])->getParameters();
        $this->assertSame(2, count($params));
        list($googleFileName, $todosToSave) = $params;
        $this->assertSame('todos_history.yml', $googleFileName);
        $this->assertGreaterThanOrEqual(2, count($todosToSave));
        $texts = $this->getTodosTexts($todosToSave);
        $this->assertNotContains('Xmas 18', $texts);
        $this->assertNotContains('New Year\'s Eve 18', $texts);
        $this->assertContains('Xmas 17', $texts);
        $this->assertContains('New Year\'s Eve 17', $texts);

    }

    private function getTodosTexts(array $todos): array
    {
        return array_map(function($todo) {
            return $todo->getText();
        }, $todos);
    }
}
