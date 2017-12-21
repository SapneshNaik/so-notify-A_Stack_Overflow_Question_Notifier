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
    
        $this->test();
        $this->isConnected($output);
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
     * Get RSS feed URL.
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
     * @param String $questionURL
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
     * @param string $question
     * @return this
     */
    public function notify($question)
    {

        if ($this->shouldNotify) {
            system(sprintf('/usr/bin/notify-send  "'.$question->title.'"  "'.$question->link->attributes()->href.'"'));
        }
    }

    protected function test()
    {

        $HOME = getenv('HOME');
        // $DBUS_PID =(int) shell_exec("ps ax | grep gconfd-2 | grep -v grep | awk '{ print $1 }'");
        // $NOTIFY_SEND_BIN="/usr/bin/notify-send";

        // $a = "grep -z DBUS_SESSION_BUS_ADDRESS /proc/$DBUS_PID/environ | sed -e s/DBUS_SESSION_BUS_ADDRESS=//";
        // echo $a;
        // $DBUS_SESSION= strval(shell_exec($a));

        // $c = sprintf("DBUS_SESSION_BUS_ADDRESS=%s /usr/bin/notify-send \"TITLE\" \"MESSAGE\"", $DBUS_SESSION);
        if (is_executable('./notify-send.sh')) {
            system('./notify-send.sh ad dd 2> /dev/null');
        }


        exit();
    }
}
