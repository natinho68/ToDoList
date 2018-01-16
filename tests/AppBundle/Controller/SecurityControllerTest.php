<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;


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

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}