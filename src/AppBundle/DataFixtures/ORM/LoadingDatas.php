<?php

namespace AppBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;


class LoadingDatas {

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getDatas($file)
    {
        $fixturesPath = __DIR__ . '/fixtures/';
        $fixtures = Yaml::parse(file_get_contents( $fixturesPath .  $file .'.yml', true));
        return $fixtures;
    }

    public function loadUsers()
    {

        $user = $this->getDatas('users');
        foreach ($user['User'] as $reference => $columns)
        {
            $user = new User();
            $user->setUsername($columns['username']);
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $columns['password']);
            $user->setPassword($password);
            $user->setEmail($columns['email']);
            $user->setRole($columns['role']);
            $this->em->persist($user);
        }
        $this->em->flush();
    }

    public function loadTasks()
    {
        $task = $this->getDatas('tasks');
        foreach ($task['Tasks'] as $reference => $columns)
        {
            $task = new Task();
            $task->setCreatedAt(new \DateTime('now'));
            $task->setTitle($columns['title']);
            $task->setContent($columns['content']);
            $task->toggle($columns['done']);
            $user = 'AppBundle\Entity\User';
            $setUser = $this->em->find($user, $columns['author']);
            $task->setAuthor($setUser);
            $this->em->persist($task);
        }
        $this->em->flush();
    }

}
