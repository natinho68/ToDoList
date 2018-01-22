<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use AppBundle\Command\LoadDatasCommand;
use Doctrine\ORM\Tools\SchemaTool;

class LoadDatasCommandTest extends WebTestCase
{

    private $client = null;

    private $em = null;

    private $container;

    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();


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

    public function test_LoadDatasCommand()
    {
        $application = new Application(static::$kernel);
        $application->add(new LoadDatasCommand());

        $command = $application->find('app:load-datas');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'      => $command->getName(),
            ));

        $this->assertRegExp('/Datas successfully loaded/', $commandTester->getDisplay());
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }
}