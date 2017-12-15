<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveTag extends CommonTasks
{

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('tag:remove')
             ->setDescription('Remove tag with the specified id')
             ->addArgument('Id', InputArgument::REQUIRED, 'Id of the tag');
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
        $Id = $input->getArgument('Id');
        $tag = $this->database->checkId('tags', $Id);
        if (!empty($tag)) {
            $this->database->query(
                'delete from tags where Id = :Id',
                compact('Id')
            );
            $this->output($output, 'Unsubscribed from '.$tag[0]['title'].'!', 'info');
        } else {
            $this->output($output, 'No tag with Id '.$Id.' exists!', 'error');
        }

        $this->output($output, 'Currently Subscribed tags', 'comment');
        $this->showTags($output);
    }
}
