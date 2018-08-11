<?php

namespace APPointer\Command;

require_once __DIR__ . '/src/bootstrap.php';

use APPointer\Command\TodoCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new TodoCommand());
$application->run();
