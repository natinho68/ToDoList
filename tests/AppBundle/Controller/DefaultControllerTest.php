<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function test_indexAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/',  array(), array(), array(
            'PHP_AUTH_USER' => 'nathan',
            'PHP_AUTH_PW'   => 'nathan',
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Bienvenue sur Todo List', $crawler->filter('h1')->text());
    }
}