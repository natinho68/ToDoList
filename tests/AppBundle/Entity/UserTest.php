<?php

namespace Tests\AppBundle\Entity;

use Tests\AppBundle\TestHelperTrait;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase{

    use TestHelperTrait;

    public function setUp()
    {
        $this->setUpWithSchema();
    }

    public function test_User(){

        $user = new User();
        $user->setUsername('UserAuthor');
        $user->setPassword('UserAuthor');
        $user->setRole('ROLE_USER');
        $user->setEmail('UserAuthor@test.com');
        $this->em->persist($user);
        $this->em->flush();


        $this->assertSame(1, $user->getId());
        $this->assertSame('UserAuthor', $user->getUsername());
        $this->assertSame('UserAuthor', $user->getPassword());
        $this->assertSame(array('ROLE_USER'), $user->getRoles());
        $this->assertEquals('UserAuthor@test.com', $user->getEmail());
        $this->assertNull($user->eraseCredentials());

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}