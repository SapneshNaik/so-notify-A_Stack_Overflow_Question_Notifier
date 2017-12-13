<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;

class CheckFeed extends CommonTasks
{


    private $questionNumber = '';
    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('run')
             ->setDescription('Check for new questions');
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
    
        $this->isConnected($output);
        $FeedURLs = $this->getFeedURLs($output);

        $tagQuestions = array();

        foreach ($FeedURLs as $FeedURL) {
            $xml = @simplexml_load_file($FeedURL);
            array_push($tagQuestions, $xml);
        }

        foreach ($tagQuestions as $tagQuestion) {
            foreach ($tagQuestion->entry as $entry) {
                // exec(sprintf('notify-send  "'.$entry->title.'"  "'.$entry->link->attributes()->href.'"'));
                $this->getQuestionNumber($entry->id)->checkDB();
             // $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s+', '2013-02-13T08:35:34.195Z');
             // var_dump($datetime);
             // echo $cur = DateTime::createFromFormat('Y-m-d\TH:i:s+');
            }
        }
        // foreach ($xml->entry as $key => $entry) {
        //     // exec(sprintf('notify-send  "'.$entry->title.'"  "'.$entry->link->attributes()->href.'"'));
        //  // $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s+', '2013-02-13T08:35:34.195Z');
        //  // var_dump($datetime);
        //  // echo $cur = DateTime::createFromFormat('Y-m-d\TH:i:s+');
        // }
    }

    /**
     * Return RSS feed URL.
     *
     * @param OutputInterface $output
     * @return mixed
     */
    protected function getFeedURLs(OutputInterface $output)
    {
        $tags = $this->database->fetchAll('tags');
        
        if ($tags) {
            $feedURLs = array();

            foreach ($tags as $tag) {
                $feedURL = sprintf("https://stackoverflow.com/feeds/tag?tagnames=".$tag['title']."&sort=newest");

                array_push($feedURLs, $feedURL);
            }
            return $feedURLs;
        } else {
            $this->output($output, 'You have not subscribed to any tags!! Exiting now..', 'error');

            exit(1);
        }
    }


    protected function getQuestionNumber($path)
    {

        $this->questionNumber = basename($path);
        return $this;
    }

    protected function checkField()
    {
        
        $IDq = $this->connection->query("SELECT * FROM questions WHERE question_number= '$this->questionNumber'");
        $IDq->setFetchMode(PDO::FETCH_ASSOC);
        $IDf = $IDq->fetch();
        if ($IDf[$item_type]) {
            return true;
        } else {
            return false;
        }
    }
}
