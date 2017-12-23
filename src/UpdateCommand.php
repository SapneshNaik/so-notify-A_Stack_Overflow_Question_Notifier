<?php namespace KernelDev;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    const MANIFEST_FILE = 'https://raw.githubusercontent.com/SapneshNaik/stack_overflow-notifier/master/src/scripts/manifest.json';

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Updates Stack Overflow Notifier to the latest version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}
