<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\LoadDatasCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\ORM\Tools\SchemaTool;

class CreateUserCommandTest extends WebTestCase

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

    public function test_loadExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new LoadDatasCommand());

        $command = $application->find('app:load-datas');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName()));

        $output = $commandTester->getDisplay();
        $this->assertContains('Datas successfully loaded!', $output);

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}