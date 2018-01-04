<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LoadDatasCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:load-datas')

            // the short description shown while running "php bin/console list"
            ->setDescription('Load datas fixtures.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to load all fixtures in the db...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $createDB = $this->getApplication()->find('doctrine:database:create');
        $loadingSchema = $this->getApplication()->find('doctrine:schema:update');
        $args = array(
            'command' => 'doctrine:schema:update',
            '-f' => true
        );

        $commandInput = new ArrayInput($args);
        $createDB->run($input, $output);
        $loadingSchema->run($commandInput, $output);
        $loadingDatas = $this->getContainer()->get('app_bundle.command');
        $loadingDatas->loadUsers();
        $loadingDatas->loadTasks();
        $output->writeln('Datas successfully loaded!');
    }
}
