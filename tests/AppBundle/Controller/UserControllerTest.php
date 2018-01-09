<?php

// tests/AppBundle/Controller/DefaultControllerTest.php
namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
{
    private $client = null;

    private $em = null;

    private $container;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->container = $this->client->getContainer();

        $this->em = $this->container->get('doctrine')->getManager();


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
        $crawler = $this->client->request('GET', '/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('html:contains("Liste des utilisateurs")')->count());
    }


    public function test_AddUser()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[roles]'] = 'ROLE_USER';
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'test@email.com';
        $crawler = $this->client->submit($form);

        $crawler = $this->client->followRedirect();


        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
    }

    public function test_EditUser()
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

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}