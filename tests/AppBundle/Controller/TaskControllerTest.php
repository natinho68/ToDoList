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

        $token = new UsernamePasswordToken('admin', null, $firewallContext, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function test_listAction()
    {
        $this->logIn();
        $this->client->request('GET', '/tasks');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }


    public function test_AddTask()
    {
       /* $createATaskUser = new User();
        $createATaskUser->setUsername('createATaskUser');
        $createATaskUser->setPassword('createATaskUser');
        $createATaskUser->setRoles(array('ROLES_USER'));
        $createATaskUser->setEmail('createATaskUser@test.com');

        $this->em->persist($createATaskUser);

        $createATask = new Task();
        $createATask->setTitle('createTask');
        $createATask->setContent('createTaskContent');
        $createATask->setAuthor($createATaskUser);

        $this->em->persist($createATask);
        $this->em->flush();
       */

        $this->logIn();
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());


        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'createTaskTitle';
        $form['task[content]'] = 'createTaskContent';

        // need to inject an user !

        $this->client->submit($form);

        /*$crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());*/

    }

    public function test_toggleTaskAction()
    {
        $testTaskUser = new User();
        $testTaskUser->setUsername('testTaskUser');
        $testTaskUser->setPassword('testTaskUser');
        $testTaskUser->setRoles(array('ROLES_USER'));
        $testTaskUser->setEmail('testTaskUser@test.com');
        $this->em->persist($testTaskUser);

        $taskTest = new Task();
        $taskTest->setTitle('TaskTest');
        $taskTest->setContent('Create a task test');
        $taskTest->setAuthor($testTaskUser);
        $this->em->persist($taskTest);

        $this->em->flush();

        $this->logIn();
        $this->client->request('GET', 'tasks/1/toggle');
        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
    }

    public function test_editAction()
    {
        
    }



    public function test_deleteAction()
    {
        $createUserTestDeleteTask = new User();
        $createUserTestDeleteTask->setUsername('createATaskUser');
        $createUserTestDeleteTask->setPassword('createATaskUser');
        $createUserTestDeleteTask->setRoles(array('ROLES_USER'));
        $createUserTestDeleteTask->setEmail('createATaskUser@test.com');
        $this->em->persist($createUserTestDeleteTask);

        $createATaskDeleteTest = new Task();
        $createATaskDeleteTest->setTitle('createTask');
        $createATaskDeleteTest->setContent('createTaskContent');
        $createATaskDeleteTest->setAuthor($createUserTestDeleteTask);
        $this->em->persist($createATaskDeleteTest);

        $this->em->flush();

        $this->logIn();
        $this->client->request('GET', 'tasks/1/delete');
        $crawler = $this->client->followRedirect();
        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}