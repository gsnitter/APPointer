<?php

namespace APPointer\Command;
require_once __DIR__ . '/../bootstrap.php';

use APPointer\Entity\GoogleFile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Helper\Table;
// use Symfony\Component\Console\Helper\TableCell;
// use Symfony\Component\Console\Formatter\OutputFormatterStyle;
// use Symfony\Component\Validator\Validation;
// use Symfony\Component\Yaml\Yaml;

use APPointer\Lib\MediaCenter;
use APPointer\Lib\TodoMerger;
use APPointer\Lib\DI;
use APPointer\Lib\TodosFileParser;
use Symfony\Component\Console\Helper\Table;
use APPointer\Entity\Todo;
use APPointer\Entity\TodoString;
use Symfony\Component\Validator\Validation;
use APPointer\Lib\Normalizer;
// use APPointer\Entity\TodoString;
// use APPointer\Lib\Normalizer;
// use APPointer\Entity\Todo;
// use APPointer\Entity\DzenMessage;
// use APPointer\Lib\DI;
// use APPointer\Lib\AtJobs\AtJobs;
// use APPointer\Lib\TodosHistorizer;
use APPointer\Lib\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use APPointer\Lib\AtJobs\AtJobs;

class APPointCommand extends Command
{
    private $output;
    private $container;

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
Creates a command to add an appointment.
ADD_HELP
            )
            ->addOption('edit', 'e', InputOption::VALUE_NONE)
            ->addOption('download', null, INPUTOPTION::VALUE_NONE,
<<<ADD_HELP
Mounts the Google-MediaCenter drive, if it is not mounted yet.
Downloads the todos.yml if it exists.
ADD_HELP
            )
            ->addOption('create-at-jobs', 'c', INPUTOPTION::VALUE_NONE,
<<<ADD_HELP
Insert respectively updates the at jobs already created.
ADD_HELP
            )
            ->addOption('upload', null, INPUTOPTION::VALUE_NONE,
<<<ADD_HELP
Mounts the Google-MediaCenter drive, if it is not mounted yet.
Uploads the changes of the loca todos.yml if it exists.
ADD_HELP
            )
            ->addOption('test', null, InputOption::VALUE_NONE)
            ->addOption('show', 's', InputOption::VALUE_NONE)
            ->addOption('umount', null, InputOption::VALUE_NONE)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;
        $this->container = DI::getContainer();

        $commands = ['download', 'upload', 'add', 'edit', 'test', 'show', 'create-at-jobs', 'umount'];
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

    private function createAtJobs()
    {
        DI::getContainer()->get(AtJobs::class)->create();
    }

    private function executeAdd(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--add') || $input->hasParameterOption('-a')) {
            $this->add($input->getOption('add'));
        }
    }

    // TODO: If error, execute should return an error code.
    private function download()
    {
        $this->mountMediaCenterAnd('merge');
    }
    
    private function upload()
    {
        $this->output->writeln(
            '<info>Remote file successfully updated</info>',
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        $this->mountMediaCenterAnd('remerge');
        // Without this, we get no sync with the WebDAV.
        $this->container->get(MediaCenter::class)
            ->umount();

        $this->output->writeln(
            '<info>Remote file successfully updated</info>',
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );
    }

    private function mountMediaCenterAnd(string $action): bool
    {
        $mediaCenter = $this->container->get(MediaCenter::class);
        if ($mediaCenter->mount()) {
            $merger = $this->container->get(TodoMerger::class);
            call_user_func([$merger, $action]);
            return false;
        } else {
            $this->output->writeln('<error>Unable to mount GMX drive</error>');
            return false;
        }
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

        // Das normalisiert gleich
        $errors = DI::getValidator()->validate($todo, null, ['Add']);

        if (count($errors) > 0) {
            $this->displayErrors($errors);
            return;
        }

        // In jedem Fall updaten wir das lokale File
        $localPath = DI::getLocalPath();
        $fs = $this->container->get(Filesystem::class);
        $oldFileArray = $fs->loadYaml($localPath);
        $oldFileArray[$todo->getNormalizedCreatedAt()] = $todo->getArrayRepresentation();
        $fs->dumpYaml($localPath, $oldFileArray);
    }

    private function edit()
    {
        $path = DI::getLocalPath();
        exec("vim {$path} > `tty`");
    }

    private function show() {
        $todoArray = $this->container
            ->get(TodosFileParser::class)
            ->getDueTodos();

        if ($todoArray) {
            // TODO SNI: Kapseln
            $table = new Table($this->output);
            $table
                ->setStyle('borderless')
                ->setHeaders(['Zeit', 'Aufgabe'])
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
        $table->addRow([
            (new \DateTime($todo->getNormalizedDateString()))->format($todo->hasTime()? 'd.m.Y' : 'd.m.Y H:i:s'),
            $todo->getText(),
        ]);
    }

    private function test()
    {
        $success = $this->container->get(MediaCenter::class)->umount();
        if ($success) {
            $this->output->writeln("<info>Successfully mounted</info>");
        } else {
            $this->output->writeln("<error>Unable to mount GMX drive</error>");
        }
        // echo "\nAll tags:\n";
        // var_dump($this->container->findTags());
// 
        // $tag = 'validator.constraint_validator';
        // echo "\nServices tagged with {$tag}:\n";
        // var_dump(DI::getContainer()->findTaggedServiceIds($tag));
// 
        // var_dump(DI::getContainer()->get('APPointer\Constraints\DateStringNormalizerValidator'));
    }

    private function umount()
    {
        $success = $this->container->get(MediaCenter::class)->umount();

        if ($success) {
            $this->output->writeln('<info>Successfully mounted</info>', OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $this->output->writeln('<error>Unable to mount GMX drive</error>', OutputInterface::VERBOSITY_VERBOSE);
        }
    }
}
