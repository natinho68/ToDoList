<?php


namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\TestHelperTrait;

class DefaultControllerTest extends WebTestCase
{
    use TestHelperTrait;

    public function setUp()
    {
        $this->createAClient();
    }

    public function test_indexAction()
    {
        $this->loginUser();
        $crawler = $this->client->request('GET', '/');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('html:contains("Bienvenue sur Todo List")')->count());
    }


    /**
     * @dataProvider getUrls
     */
    public function test_SecureUrls($url)
    {

        $this->client->request('GET', $url);
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(
            'http://localhost/login',
            $response->getTargetUrl(),
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }


    /**
     * @dataProvider getAdminUrls
     */
    public function test_NotAllowedRoles($url)
    {
        $this->loginUser();

        $this->client->request('GET', $url);
        $this->assertSame(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode(),
            sprintf('The %s URL return correctly forbidden.', $url)
        );

    }

    public function getUrls()
    {
        yield ['/'];
        yield ['/tasks/create'];
        yield ['/users/create'];
        yield ['/tasks/1/edit'];
        yield ['/users/1/edit'];

    }

    public function getAdminUrls()
    {
        yield ['/users/create'];
        yield ['/users/create'];
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}