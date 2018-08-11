<?php

namespace APPointer;

use Symfony\Component\Dotenv\Dotenv;

use APPointer\Lib\DI;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new DotEnv();
$dotenv->load(DI::getProjectPath() . '/.env');
