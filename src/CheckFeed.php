<?php namespace KernelDev;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;

class CheckFeed extends CommonTasks
{


    /**
     * The current question number.
     *
     * @var string
     */
    private $questionNumber = '';

    /**
     * A flag to denote if the question already exists.
     *
     * @var boolean
     */
    private $questionExists;

    /**
     * A flag to denote if the question should be notified.
     *
     * @var boolean
     */
    private $shouldNotify;

    /**
     * Store the path name of the bash script.
     *
     * @var string
     */
    private $PATH;

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
        $this->checkNotificationScipt($output);
        $FeedURLs = $this->getFeedURLs($output);

        $tagQuestions = array();

        foreach ($FeedURLs as $FeedURL) {
            $xml = @simplexml_load_file($FeedURL);
            array_push($tagQuestions, $xml);
        }

        foreach ($tagQuestions as $tagQuestion) {
            foreach ($tagQuestion->entry as $question) {
                $this->getQuestionNumber($question->id)
                     ->questionExists()
                     ->shouldPersist()
                     ->notify($question);
            }
        }
    }

    /**
     * Generate RSS feed URL for added tags.
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
            // exec(sprintf('notify-send  sad'));

            exit(1);
        }
    }

    /**
     * Get Question number from question URL.
     *
     * @param $questionURL
     * @return this
     */
    protected function getQuestionNumber($questionURL)
    {

        $this->questionNumber = basename($questionURL);
        return $this;
    }

    /**
     * Check if the question exists in the database.
     *
     * @param
     * @return this
     */
    protected function questionExists()
    {
        
        $IDq = $this->database->checkField('questions', $this->questionNumber);
        
        if (!empty($IDq)) {
            $this->questionExists =true;
            return $this;
        } else {
            $this->questionExists =false;
            return $this;
        }
    }

    /**
     * Check if the question should be persisted in database
     * The question is only persisted if it's not already in the database
     * If the question exists in db then it question has already been notified
     *
     * @param
     * @return this
     */
    protected function shouldPersist()
    {
     
        if (!$this->questionExists) {
            $questionNumber = $this->questionNumber;
            $IDq = $this->database->query(
                'insert into questions(question_number) values(:questionNumber)',
                compact('questionNumber')
            );
            $this->shouldNotify = true;
        } else {
            $this->shouldNotify = false;
        }

        return $this;
    }

    /**
     * Send a system notification with question name as title
     * and a link to the question as the notification summary
     *
     * @param $question
     * @return this
     */
    public function notify($question)
    {

        if ($this->shouldNotify) {
            $command = sprintf('%s "%s" "%s" 2> /dev/null', $this->PATH, $question->title, $question->link->attributes()->href);
            system($command);
        }
    }

    /**
     * Check if the so-notify.sh file exists. If not download
     * it from github repository and make it executable.
     *
     * @param OutputInterface $output
     * @return this
     */
    protected function checkNotificationScipt(OutputInterface $output)
    {

        $HOME = getenv('HOME');

        $PATH = $HOME.'/.so-notify.sh';

        if (!file_exists($PATH)) {
            $this->output($output, 'First Run, Fetching your distribution specfic configuration', 'comment');

            if (!@copy('https://raw.githubusercontent.com/SapneshNaik/stack_overflow-notifier/master/notify-send.sh', $PATH)) {
                $this->output($output, 'There was an error fetching your distribution specfic configuration!', 'error');
            } else {
                $this->output($output, 'Done. Continuing...', 'info');
            }
        }

        if (!is_executable($PATH)) {
             chmod($PATH, 0755);
        }

        $this->PATH = $PATH;
        return $this;
    }
}
