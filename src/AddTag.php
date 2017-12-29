<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddTag extends CommonTasks
{

    /**
     * input tag.
     *
     * @var string
     */
    protected $tag;

    /**
     * A flag to denote if the tag already exists.
     *
     * @var boolean
     */
    private $tagExists;

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

        if ($this->tagExists()) {
            $this->output($output, 'Tag '.$this->tag.' already exists!', 'error');
            exit(1);
        }


        $this->database->query(
            'insert into tags(title) values(:tag)',
            compact('tag')
        );
        $this->output($output, 'Subscribed to '.$this->tag.'!', 'info');
        $this->showTags($output);
    }

    /**
     * Check if the tag is valid.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function isValidTag($output, $input)
    {
        $this->output($output, 'Verifying if the tag is valid...', 'comment');

        $this->tag = strtolower($input->getArgument('name'));

        $feedURL = sprintf("https://stackoverflow.com/feeds/tag?tagnames=".$this->tag."&sort=newest");

        $xml = @simplexml_load_file($feedURL);

        if ($xml === false) {
            $this->output($output, 'Invalid Tag!!', 'error');

            exit(1);
        }
    }

    /**
     * Check if the tag already exists in the database.
     *
     * @return this
     */
    protected function tagExists()
    {
        $IDq = $this->database->checkField('tags', 'title', $this->tag);

        if (!empty($IDq)) {
            return true;
        } else {
            return false;
        }
    }
}
