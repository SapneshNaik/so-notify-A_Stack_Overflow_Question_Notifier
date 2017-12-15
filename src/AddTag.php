<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddTag extends CommonTasks
{

    protected $tag;
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('tag:add')
             ->setDescription('Subscribe to a new tag')
             ->addArgument('name', InputArgument::REQUIRED, 'Name of the tag');
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

        $this->isConnected($output)
             ->isValidTag($output, $input);

        $tag = $this->tag;
        $this->database->query(
            'insert into tags(title) values(:tag)',
            compact('tag')
        );
        $this->output($output, 'Subscribed to '.$this->tag.'!', 'info');
        $this->showTags($output);
    }

    protected function isValidTag($output, $input)
    {
        $this->output($output, 'Verifying if the tag is valid...', 'comment');

        $this->tag = $input->getArgument('name');

        $feedURL = sprintf("https://stackoverflow.com/feeds/tag?tagnames=".$this->tag."&sort=newest");

        $xml = @simplexml_load_file($feedURL);

        if ($xml === false) {
            $this->output($output, 'Invalid Tag!!', 'error');

            exit(1);
        }
    }
}
