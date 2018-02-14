<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\TestHelperTrait;


class SecurityControllerTest extends WebTestCase
{
    use TestHelperTrait;

    public function setUp()
    {
        $this->setUpWithSchema();
    }

    public function test_loginAction()
    {

        $userTestLogin = new User();
        $userTestLogin->setUsername('userTestLogin');
        $plainPass = 'userTestLogin';
        $password = $this->container->get('security.password_encoder')->encodePassword($userTestLogin, $plainPass);
        $userTestLogin->setPassword($password);

        $userTestLogin->setRole('ROLE_USER');
        $userTestLogin->setEmail('userTestLogin@test.com');

        $this->em->persist($userTestLogin);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form.login_form')->count());


        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $userTestLogin->getUsername();
        $form['_password'] = $plainPass;

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('html:contains("Bienvenue sur Todo List")')->count());

    }

    public function test_badCredentials()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form.login_form')->count());


        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'bad';
        $form['_password'] = 'credentials';

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.alert.alert-danger:contains("Invalid credentials")')->count());
    }


    public function test_logoutCheck()
    {
        $this->logInAdmin();
        $this->client->request('GET', '/logout');

        $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(
            'http://localhost/login',
            $response->getTargetUrl()
        );

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}