<?php

namespace SniTodos\Command;
require_once __DIR__ . '/../bootstrap.php';

use SniTodos\Entity\GoogleFile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use SniTodos\Lib\Normalizer;
use SniTodos\Entity\Todo;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Validator\Validation;
use SniTodos\Entity\TodoString;
use Symfony\Component\Yaml\Yaml;

class TodoCommand extends Command
{
    private $output;

    protected function configure()
    {
        $this
            ->setName('todo')
            ->setDescription('Manage todos')
            ->addOption('add', 'a', INPUTOPTION::VALUE_OPTIONAL,
<<<ADD_HELP
Example Usage: execute todo:add '12-24, Weihnachten, 6 days, privat, 1 year'
Creates a file, and opens it with \$EDITOR.
ADD_HELP
            )

            ->addOption('show', 's', InputOption::VALUE_NONE)
            ->addOption('download', 'd', InputOption::VALUE_OPTIONAL, 
<<<ADD_HELP
Downloads the google files into the file cache path.
Defaults only to download todos.yml, can be overridden by passing a list of files
to download like 'todo.yml, oldTodos.yml'
ADD_HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;

        // Ugly, but this seems the only sensible way to handle optional options.
        if ($input->hasParameterOption('--download') || $input->hasParameterOption('-d')) {
            $this->download($input->getOption('download') ?? '');
        }

        if ($input->hasParameterOption('--add') || $input->hasParameterOption('-a')) {
            $this->add($input->getOption('add'));
        }

        if ($input->getOption('show')) {
            $this->showTodos();
         }
    }

    private function add(string $todoString)
    {
        $todoString = new TodoString($todoString);
        $todoArray = $todoString->toArray($todoString);

        // do {
            // Create File from $todoArray
            // Let User edit File (exec("vim $tmpTodoYml > `tty`"))
            // $todoArray = ...
            // $errors berechnen, dafür wird ein Todo kreiert
            $todo = Todo::createFromArray($todoArray);

            $this->validator = Validation::createValidatorBuilder()
                ->addMethodMapping('loadValidatorMetadata')
                ->getValidator();
            $errors = $this->validator->validate($todo);
        // } while (count($error) > 0);

        if (count($errors) > 0) {
            // TODO SNI
            var_dump($errors);
            return;
        }

        $todoFile = new GoogleFile('todos.yml');
        $oldFileArray = $todoFile->parseYaml($todoFile);
        $normalizer = Normalizer::getInstance();
        $normalizer->normalize($todo);
        array_unshift($oldFileArray, $todo->getArrayRepresentation());

        $todoFile->updateYaml($oldFileArray);

        // Todo-Yaml erstellen und an das gecachte File anhängen, falls es existiert
        // Todo hochladen
    }

    private function download(string $filesString)
    {
        $filesString = $filesString? : 'todos.yml';
        $fileNames = preg_split('@(, |,| )@', $filesString);

        foreach ($fileNames as $fileName) {
            $file = new GoogleFile($fileName);

            if (!$file->exists()) {
                $this->output->writeln("<error>File {$fileName} does not exist on google drive</error>");
                continue;
            }

            $file->copyToFileCache();
            $this->output->writeln("<bg=green>File {$fileName} copied to " . GoogleFile::getFileCache() . "</>");
        }
    }

    private function showTodos()
    {
        // TODO SNI: Später am besten den Cache benutzen
        // Dazu ungefähr das benutzen: $lastModified = (new \DateTime())->setTimestamp(filemtime('composer.lock'));

        $todosFile = new GoogleFile('todos.yml');
        $todosArray = $todosFile->parseYaml();

        // TODO SNI: Kapseln
        $table = new Table($this->output);
        $table
            ->setStyle('borderless')
            ->setHeaders(['Zeit', 'Aufgabe'])
            ;
        // Unfortunately, the table class has no getter for row count, and since rows are private,
        // we can't even subclass it.
        $rowCount = 0;

        foreach ($todosArray as $todoArray) {
            $todo = Todo::createFromArray($todoArray);

            Normalizer::getInstance()->normalize($todo);

            if ($todo->isDue()) {
                $rowCount++;
                $this->showTodo($todo, $table);
                // TODO SNI: Später sollten wir alte Todos loggen
                // } elseif ($todo->isOld()) {
                // $this->logOldTodo($oldTodo);
            }
        }

        if ($rowCount > 0) {
            $table->render();
        } else {
            $this->output->writeln("<bg=green>Keine offenen Todos.</>");
        }
    }

    private function showTodo(Todo $todo, Table $table)
    {
        // nmap _ :w<cr>;!/home/snitter/Projekte/GoogleClient/execute todo -s<cr>
        $table->addRow([
            (new \DateTime($todo->getNormalizedDateString()))->format($todo->hasTime()? 'd.m.Y' : 'd.m.Y H:i:s'),
            $todo->getText(),
        ]);
    }
}
