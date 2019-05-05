<?php

namespace APPointer\tests\Entity;

use PHPUnit\Framework\TestCase;
use APPointer\Entity\Todo;
use APPointer\Repository\TodoRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TodoRepositoryTest extends WebTestCase {

    public function setUp() {
        self::bootKernel();
        $container = self::$container;
        $this->repository = $container
            ->get('doctrine')
            ->getManager('default')
            ->getRepository(Todo::class);
    }

    public function testFindPossiblyDueTodosQB()
    {
        $sql = $this->repository->findPossiblyDueTodosQB()->getQuery()->getSql(); 
        $expected = 'WHERE (t0_.display_interval > ? AND t0_.date >= ?) OR (t0_.display_interval <= ? AND t0_.date <= ? AND t0_.date >= ?)';
        $this->assertContains($expected, $sql);
    }
}
