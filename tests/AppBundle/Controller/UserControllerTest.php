<?php

// tests/AppBundle/Controller/DefaultControllerTest.php
namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\TestHelperTrait;

class UserControllerTest extends WebTestCase
{
    use TestHelperTrait;

    public function setUp()
    {
        $this->setUpWithSchema();
    }

    public function test_AddUserEmptyRole()
    {

        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'test@email.com';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains(" Vous devez choisir un rôle")')->count());

    }

    public function test_AddUserEmptyEmail()
    {

        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("Vous devez saisir une adresse email.")')->count());

    }

    public function test_AddUserNotValidEmail()
    {

        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'testemailcom';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("Le format de l\'adresse n\'est pas correcte.")')->count());
    }

    public function test_AddUserNotSamePasswords()
    {

        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test2';
        $form['user[email]'] = 'test@email.com';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("This value is not valid.")')->count());
    }


    public function test_AddUserEmptyPassword()
    {

        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');


        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[roles]'] = 'ROLE_USER';
        $form['user[username]'] = 'testUser';
        $form['user[email]'] = 'test@email.com';

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('html:contains("Vous devez saisir un mot de passe.")')->count());

    }

    public function test_listAction()
    {
        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('html:contains("Liste des utilisateurs")')->count());
    }


    public function test_AddUser()
    {
        $this->logInAdminObject();
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[roles]'] = 'ROLE_USER';
        $form['user[username]'] = 'testUser';
        $form['user[password][first]'] = 'test';
        $form['user[password][second]'] = 'test';
        $form['user[email]'] = 'test@email.com';
        $this->client->submit($form);

        $crawler = $this->client->followRedirect();


        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
    }

    public function test_EditUser()
    {
        $this->logInAdminObject();

        $getUserTest = $this->em->getRepository(User::class)->find(1);
        $getId = $getUserTest->getId();

        $crawler = $this->client->request('GET', 'users/'. $getId .'/edit');

        $form = $crawler->selectButton('Modifier')->form();

        $form['user[roles]'] = 'ROLE_USER';
        $form['user[username]'] = 'UserEdited';
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