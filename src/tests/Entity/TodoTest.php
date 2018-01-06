<?php

namespace SniTodos\tests\Entity;

use PHPUnit\Framework\TestCase;
use SniTodos\Entity\Todo;
use Symfony\Component\Validator\Validation;

class TodoTest extends TestCase {

    public function setUp()
    {
        $this->todo = new Todo();
        $this->todo
            ->setDisplayTime('1 d');
        $this->validator = Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
    }

    public function testValidateDateStringEmptyValue()
    {
        $errors = $this->validator->validate($this->todo);
        $this->assertEquals(1, count($errors));

        $error = $errors[0];
        $this->assertSame('dateString', $error->getPropertyPath());
        $this->assertContains('should not be blank', $error->getMessage());
    }

    public function testValidateDateStringGoodStrings()
    {
        $goodStrings = [
            '24.12.2017 00:00:00',
            '25.12.',
            '23.12.',
            '23.12. 10:00',
        ];

        foreach ($goodStrings as $goodString) {
            $this->todo->setDateString($goodString);
            $errors = $this->validator->validate($this->todo);

            $errorStrings = [];
            foreach ($errors as $error) {
                $errorStrings[] = $error->getMessage();
            }
            $errorString = implode(', ', $errorStrings);
            $this->assertSame(0, count($errors), "Unerwarteter Fehler bei DateString {$goodString}: " . $errorString);
        }
    }

    public function testValidateDateStringBadStrings()
    {
        $badStrings = [
            'bla',
            // Interessanterweise wird der 31.11. akzeptiert 
            '32.11.2017',
        ];

        foreach ($badStrings as $badString) {
            $this->todo->setDateString($badString);
            $errors = $this->validator->validate($this->todo);

            $errorStrings = [];
            foreach ($errors as $error) {
                $errorStrings[] = $error->getMessage();
            }
            $errorString = implode(', ', $errorStrings);

            $this->assertGreaterThan(0, count($errors));
        }
    }

    private function getArrayRepresentation()
    {
        return [
            'dateString' => '31.12.2017',
            'text' => 'Party bei Andi',
            'displayTime' => '2d',
        ];
    }

    public function testCreateFromArray()
    {
        $todo = Todo::createFromArray($this->getArrayRepresentation());
        $this->assertSame('31.12.2017', $todo->getDateString());
        $this->assertSame('Party bei Andi', $todo->getText());
        $this->assertSame('2d', $todo->getDisplayTime());

        return $todo;
    }

    /**
     * @depends testCreateFromArray
     */
    public function testGetArrayRepresentation(Todo $todo)
    {
        $array = $todo->getArrayRepresentation();
        $this->assertSame(5, count($array));
        $expectedArray = $this->getArrayRepresentation();

        foreach ($expectedArray as $key => $value) {
            $this->assertSame($array[$key], $value);
        }
    }

    public function testIsDueNoTimeString()
    {
        $this->todo
            ->setNormalizedDateString('31.12.2017 23:59:59')
            ->setNormalizedDisplayTime('P2D');

        $this->assertTrue( $this->todo->isDue(new \DateTime('31.12.2017 10:00:00')));
        $this->assertFalse($this->todo->isDue(new \DateTime('01.01.2018 00:00:00')));
        $this->assertTrue( $this->todo->isDue(new \DateTime('30.12.2017 00:00:00')));
        $this->assertTrue( $this->todo->isDue(new \DateTime('29.12.2017 10:00:00')));
        $this->assertFalse( $this->todo->isDue(new \DateTime('28.12.2017 10:00:00')));
    }
}
