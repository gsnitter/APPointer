<?php

namespace APPointer\Command;

use APPointer\Entity\GoogleFile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Validator\Validation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

use APPointer\Lib\CronHandler;
use APPointer\Lib\TodoMerger;
use APPointer\Lib\DI;
use APPointer\Lib\TodosFileParser;
use APPointer\Entity\Todo;
use APPointer\Entity\TodoString;
use APPointer\Lib\Normalizer;
use APPointer\Lib\Filesystem;
use APPointer\Lib\AtJobs\AtJobs;
use APPointer\Repository\TodoRepository;
use Sni\ExtendedOutputBundle\Service\ExtendedOutput;
use APPointer\Entity\AlarmTime;

class APPointCommand extends Command
{
    private $output;
    private $container;

    public function __construct(ContainerInterface $container, ExtendedOutput $eOutput)
    {
        $this->container = $container;
        $this->eOutput = $eOutput;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('appoint')
            ->setDescription('Manage appointments')
            ->addOption('add', 'a', INPUTOPTION::VALUE_REQUIRED,
<<<ADD_HELP
Example Usages:
  - execute appoint:add '23:00; Go to bed; 1 d; 22:50 green/22:55/23:00 red'
  - execute appoint:add 'today; Call John Doe; 0d'
  - execute appoint:add 'heute; Dzen-Messages-Commands erstellen ohne QA; 0d; 13:50 grün/14:30/15:30 rot'
  - execute appoint:add '59 23 * * 3; Jeden Mittwoch Kitafino; 2d'
Creates a command to add an appointment.
ADD_HELP
            )
            ->addOption('download', null, INPUTOPTION::VALUE_NONE,
<<<ADD_HELP
Updates the local todo table.
ADD_HELP
            )
            ->addOption('upload', null, INPUTOPTION::VALUE_NONE,
<<<ADD_HELP
Updates the remote todo table.
ADD_HELP
            )
            ->addOption('test', null, InputOption::VALUE_NONE)
            ->addOption('list', 'l', InputOption::VALUE_NONE)
            ->addOption('list-all', null, InputOption::VALUE_NONE)
            ->addOption('schedule-todays-alarmtimes', 's', InputOption::VALUE_NONE)
            ->addOption('show-alarm-times', null, InputOption::VALUE_NONE)
            ->addOption('hide-alarm-time', null, InputOption::VALUE_NONE)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;

        $commands = ['download', 'upload', 'add', 'show-alarm-times', 'hide-alarm-time', 'test', 'list', 'list-all', 'schedule-todays-alarmtimes'];
        $specialCommands = ['add'];

        foreach ($commands as $command) {
            $camelizedCommand = lcfirst(Container::camelize(str_replace('-', '_', $command)));

            // We treat commands with arguments 
            if (in_array($command, $specialCommands)) {
                $functionName = 'execute' . ucfirst($camelizedCommand);
                $this->$functionName($input, $output);
            } else {
                if ($input->getOption($command)) {
                    $this->$camelizedCommand();
                }
            }
        }
    }

    private function executeAdd(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--add') || $input->hasParameterOption('-a')) {
            $this->add($input->getOption('add'));
        }
    }

    private function download()
    {
        $this->container->get(TodoMerger::class)
            ->mergeRemoteToLocal();
    }
    
    private function upload()
    {
        $this->container->get(TodoMerger::class)
            ->mergeLocalToRemote();
    }

    private function displayErrors($errors)
    {
        foreach ($errors as $error) {
            $this->output->writeln('<error>' . $error->getMessage() . '</error>');
        }
    }

    private function add(string $todoString)
    {
        $todoString = new TodoString($todoString);
        $todoArray = $todoString->toArray($todoString);

        $todo = Todo::createFromArray($todoArray);

        // Here we do the normalization
        $errors = $this->container->get('validator')
            ->validate($todo, null, ['Add']);

        if (count($errors) > 0) {
            $this->displayErrors($errors);
            return;
        }

        $localEm = $this->container->get('doctrine')->getManager('default');
        $localEm->persist($todo);
        $localEm->flush();
    }

    private function getTodoRepo(string $managerName): TodoRepository
    {
        return $this->container
            ->get('doctrine')
            ->getManager($managerName)
            ->getRepository(Todo::class);
    }

    private function list() {
        $this->container->get(CronHandler::class)->resetDateStrings();
        $this->container->get('doctrine')->getManager()->flush();

        $todos = $this->getTodoRepo('default')
            ->findDueTodos();
        return $this->showSome($todos);
    }

    private function listAll()
    {
        $this->container->get(CronHandler::class)->resetDateStrings();
        $this->container->get('doctrine')->getManager()->flush();

        $todos = $this->getTodoRepo('default')
            ->findFutureTodos();

        return $this->showSome($todos);
    }

    private function showSome(array $todoArray)
    {
        if ($todoArray) {
            $table = new Table($this->output);
            $table
                ->setStyle('borderless')
                ->setHeaders(['ID', 'Zeit', 'Aufgabe'])
                ;

            foreach ($todoArray as $todo) {
                $this->showTodo($todo, $table);
            }

            $table->render();
        } else {
            $this->output->writeln('<bg=green>No unclosed todos.</>');
        }
    }

    private function showTodo(Todo $todo, Table $table)
    {
        $time = $todo->getDate()->format($todo->hasTime()? 'd.m.Y H:i:s' : 'd.m.Y');
        $daysToGo = date_diff(new \DateTime('today'), $todo->getDate())->days;
        if ($daysToGo > 1) {
            $time .= " (in {$daysToGo} Tagen)";
        }
        $table->addRow([
            $todo->getLocalId(),
            $time,
            $todo->getText(),
        ]);
    }

    private function test()
    {
        $output = $this->eOutput;
        $output->writeln('Some text with a single <info>green</info> word.');
        $table = new Table($output->getActiveOutput());
        $table->setHeaders(['One', 'Two'])
            ->setRows([[1, 2]]);
        $table->render();
        $output->writeln("<bg=yellow;options=bold>Some bold text with yellow background\nwith two lines.</>");

        // TODO SNI
        // $stream = $output->getActiveOutput()->getStream();
        // rewind($stream);
        // $content = fread($stream, 10000);

        // echo $content;

        $output->renderActiveWindow();
    }

    private function test2()
    {
        try {
            $cron = \Cron\CronExpression::factory('a0 * * * *');
            echo $cron->getNextRunDate()->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            echo $e->__toString();
        }
    }

    private function showAlarmTimes()
    {
        $alarmTimes = $this->container->get('doctrine')
            ->getManager()
            ->getRepository(AlarmTime::class)
            ->findBy(['date' => new \DateTime(date('Y-m-d H:i:00'))])
            ;
        if ($alarmTimes) {
            $texte = array_reduce($alarmTimes, function($texte, AlarmTime $alarmTime) {
                array_push($texte, $alarmTime->getParentTodo()->getText());
                return $texte;
            }, []);
            $text = count($texte) > 1 ? '  - ' . implode("\n  - ", $texte) : array_pop($texte);
            $command = 'notify-send -t 1200000 "' . date('H:i') . ' Uhr" "'. $text . '"';
            shell_exec($command);
        }
    }

    public function hideAlarmTime()
    {
        `pkill notify-osd`;
    }

    private function scheduleTodaysAlarmtimes(): void
    {
        $em = $this->container->get('doctrine')->getManager();
        $todos = $em->getRepository(Todo::class)
            ->findBy(['repeatable' => true])
            ;

        // Recalculates alarmTimes using dateString
        foreach ($todos as $todo) {
            $this->container->get('validator')->validate($todo, null, ['Add']);
        }

        foreach ($todos as $todo) {
            foreach ($todo->getAlarmTimeEntities() as $alarmTime) {
                // Via listener, this also removes its at-job, if it exists.
                $em->remove($alarmTime);
            }
        }

        $em->flush();
    }
}
