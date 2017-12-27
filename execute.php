<?php

namespace SniTodos\Command;

require_once __DIR__ . '/src/bootstrap.php';

use SniTodos\Command\TodoCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new TodoCommand());
$application->run();
