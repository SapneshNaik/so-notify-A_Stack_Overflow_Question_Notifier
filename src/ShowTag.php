<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowTag extends CommonTasks
{

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('tag:show')
             ->setDescription('Show all subscribed tags');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showTags($output);
    }
}
