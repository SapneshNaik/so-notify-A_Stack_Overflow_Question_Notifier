<?php namespace KernelDev;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommonTasks extends SymfonyCommand
{

    /**
     * The wrapper for the database.
     *
     * @var DatabaseAdapter
     */
    protected $database;

    /**
     * Create a new Command instance.
     *
     * @param DatabaseAdapter $database
     */
    public function __construct(DatabaseAdapter $database)
    {
        $this->database = $database;

        parent::__construct();
    }

    /**
     * Show a table of all tasks.
     *
     * @param OutputInterface $output
     * @return mixed
     */
    protected function showTags(OutputInterface $output)
    {
        $tags = $this->database->fetchAll('tags');

        if ($tags) {
            $table = new Table($output);

            $table->setHeaders(['Id', 'Tag Name'])
                  ->setRows($tags)
                  ->render();
        } else {
            $this->output($output, 'You have not subscribed to any tags!!', 'comment');

            exit(1);
        }
    }

    /**
     * Check if question has already been notified.
     *
     * @param OutputInterface $output
     * @return boolean
     */
    protected function checkQuestionStatus($questionNumber)
    {
        $tags = $this->database->fetchAll('questions');

        // $table = new Table($output);

        // $table->setHeaders(['Id', 'Tag Name'])
        //       ->setRows($tags)
        //       ->render();
    }


    /**
     * Check if the system has internet access.
     *
     * @param OutputInterface $output
     * @return mixed
     */
    protected function isConnected(OutputInterface $output)
    {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) {
            $is_conn = true;
            fclose($connected);
        } else {
            $this->output($output, 'No internet connection!! Exiting now..', 'error');

            exit(1);
        }
        return $is_conn;
    }

    /**
     * Output to console.
     *
     * @param OutputInterface $output
     * @param $message
     * @param $type (comment/error/info/success)
     * @return mixed
     */
    protected function output(OutputInterface $output, $message, $type)
    {

            $output->writeln('<'.$type.'>'.$message.'</'.$type.'>');
    }


    protected function checkField(){
        $this->database->fetchAll('questions');

    }
}
