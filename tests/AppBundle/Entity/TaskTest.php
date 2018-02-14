<?php

namespace Tests\AppBundle\Entity;

use Tests\AppBundle\TestHelperTrait;
use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase{

    use TestHelperTrait;

    public function setUp()
    {
        $this->setUpWithSchema();
    }

    public function test_Task(){

    $user = new User();
    $user->setUsername('UserAuthor');
    $user->setPassword('UserAuthor');
    $user->setRole('ROLE_USER');
    $user->setEmail('UserAuthor@test.com');
    $this->em->persist($user);

    $task = new Task();
    $task->setTitle('TaskTest');
    $task->setContent('Create a task test');
    $task->setAuthor($user);
    $task->setCreatedAt(New \DateTime('2018-01-12 12:30:00'));
    $this->em->persist($task);

    $this->em->flush();


    $this->assertSame(1, $task->getId());
    $this->assertSame('TaskTest', $task->getTitle());
    $this->assertSame('Create a task test', $task->getContent());
    $this->assertSame($user, $task->getAuthor());
    $this->assertFalse($task->isDone());
    $this->assertEquals(New \DateTime('2018-01-12 12:30:00'), $task->getCreatedAt());

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}