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

        $this->logIn();
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->security->getToken()->getUser();


        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'taskTest';
        $form['task[content]'] = 'contentTaskTest';

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();


        //echo $this->client->getResponse()->getContent();


    }

    /*public function test_EditUser()
    {

        $editUser = new User();
        $editUser->setUsername('editUser');
        $editUser->setPassword('editUser');
        $editUser->setRoles(array('ROLES_USER'));
        $editUser->setEmail('editUser@test.com');

        $this->em->persist($editUser);
        $this->em->flush();

        $this->logIn();
        $crawler = $this->client->request('GET', 'users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();


        $form['user[roles]'] = 'ROLE_USER';
        $form['user[username]'] = 'testUserEdited';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'test@email.com';

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());

    }*/

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}