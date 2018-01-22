<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TaskControllerTest extends WebTestCase
{
    private $client = null;

    private $em = null;

    private $container;

    private $security;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->container = $this->client->getContainer();

        $this->em = $this->container->get('doctrine')->getManager();

        $this->security = $this->container->get('security.token_storage');


        // to not to load the metadata every time
        static $metadatas;


        if(!isset($metadatas))
        {
            $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();


        if(!empty($metadatas))
        {
            $schemaTool->createSchema($metadatas);
        }


    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $testTaskUser = new User();
        $testTaskUser->setUsername('UserForLogin');
        $testTaskUser->setPassword('createUser');
        $testTaskUser->setRoles(array('ROLE_ADMIN'));
        $testTaskUser->setEmail('createUser@test.com');

        $this->em->persist($testTaskUser);
        $this->em->flush();

        $token = new UsernamePasswordToken($testTaskUser, null, $firewallContext, $testTaskUser->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('GET', '/');

    }

    private function logInUser()
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $testTaskUser = new User();
        $testTaskUser->setUsername('UserRole');
        $testTaskUser->setPassword('createUser');
        $testTaskUser->setRoles(array('ROLE_USER'));
        $testTaskUser->setEmail('UserRole@test.com');

        $this->em->persist($testTaskUser);
        $this->em->flush();

        $token = new UsernamePasswordToken($testTaskUser, null, $firewallContext, $testTaskUser->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('GET', '/');

    }

    public function test_listAction()
    {
        $this->logIn();
        $this->client->request('GET', '/tasks');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }


    public function test_AddTask()
    {

        $this->logIn();
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());


        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'createTaskTitle';
        $form['task[content]'] = 'createTaskContent';

        $this->client->submit($form);


        $crawler = $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
        $this->assertGreaterThan(0, $crawler->filter('div:contains("a été bien été ajoutée.")')->count());

    }

    public function test_AddTaskEmptyContent()
    {

        $this->logIn();
        $crawler = $this->client->request('GET', '/tasks/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'createTaskTitle';
        $form['task[content]'] = '';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("Vous devez saisir du contenu.")')->count());

    }

    public function test_AddTaskEmptyTitle()
    {

        $this->logIn();
        $crawler = $this->client->request('GET', '/tasks/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = '';
        $form['task[content]'] = 'Content';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("Vous devez saisir un titre.")')->count());

    }

    public function test_toggleTaskAction()
    {
        $this->logIn();
        $user = $this->security->getToken()->getUser();

        $taskTest = new Task();
        $taskTest->setTitle('TaskTest');
        $taskTest->setContent('Create a task test');
        $taskTest->setAuthor($user);
        $this->em->persist($taskTest);

        $this->em->flush();

        $getTaskTest = $this->em->getRepository(Task::class)->find(1);
        $getId = $getTaskTest->getId();


        $this->client->request('GET', 'tasks/'. $getId .'/toggle');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
        $this->assertGreaterThan(0, $crawler->filter('div:contains("a bien été marquée comme faite.")')->count());
    }

    public function test_deleteTaskNotTheAuthor()
    {
        $this->logInUser();
        $userAuthor = new User();
        $userAuthor->setUsername('UserAuthor');
        $userAuthor->setPassword('UserAuthor');
        $userAuthor->setRoles(array('ROLE_USER'));
        $userAuthor->setEmail('UserAuthor@test.com');
        $this->em->persist($userAuthor);


        $taskToDelete = new Task();
        $taskToDelete->setTitle('TaskToDelete');
        $taskToDelete->setContent('delete this task');
        $taskToDelete->setAuthor($userAuthor);
        $this->em->persist($taskToDelete);

        $this->em->flush();

        $getTaskToDelete = $this->em->getRepository(Task::class)->findOneBy(array('title' => 'TaskToDelete'))->getId();

        $this->client->request('GET', 'tasks/'. $getTaskToDelete .'/delete');

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-danger:contains("Vous ne pouvez pas supprimer la tâche")')->count());

    }

    public function test_editAction()
    {
        $this->logIn();
        $user = $this->security->getToken()->getUser();

        $taskTest = new Task();
        $taskTest->setTitle('TaskTest');
        $taskTest->setContent('Create a task test');
        $taskTest->setAuthor($user);
        $this->em->persist($taskTest);

        $this->em->flush();

        $getTaskTest = $this->em->getRepository(Task::class)->find(1);
        $getId = $getTaskTest->getId();


        $crawler = $this->client->request('GET', 'tasks/'. $getId .'/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'TaskTitleEdited';
        $form['task[content]'] = 'TaskContentEdited';

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
        $this->assertGreaterThan(0, $crawler->filter('div:contains("a bien été modifiée.")')->count());

    }


    public function test_deleteAction()
    {

        $this->logIn();
        $user = $this->security->getToken()->getUser();

        $TaskForDelete = new Task();
        $TaskForDelete->setTitle('TaskForDelete');
        $TaskForDelete->setContent('TaskContent');
        $TaskForDelete->setAuthor($user);
        $this->em->persist($TaskForDelete);

        $this->em->flush();

        $getTaskForDelete = $this->em->getRepository(Task::class)->find(1);
        $getId = $getTaskForDelete->getId();


        $this->client->request('GET', 'tasks/'. $getId .'/delete');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
        $this->assertGreaterThan(0, $crawler->filter('div:contains("a bien été supprimée.")')->count());

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}