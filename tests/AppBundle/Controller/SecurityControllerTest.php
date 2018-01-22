<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class SecurityControllerTest extends WebTestCase
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

    public function test_loginAction()
    {

        $userTestLogin = new User();
        $userTestLogin->setUsername('userTestLogin');
        $plainPass = 'userTestLogin';
        $password = $this->container->get('security.password_encoder')->encodePassword($userTestLogin, $plainPass);
        $userTestLogin->setPassword($password);

        $userTestLogin->setRoles(array('ROLE_USER'));
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

    public function test_logoutCheck()
    {
        $this->logIn();
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