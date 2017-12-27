<?php

namespace SniTodos;

use Symfony\Component\Dotenv\Dotenv;

use SniTodos\Entity\GoogleFile;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new DotEnv();
$dotenv->load(GoogleFile::getProjectPath() . '/.env');
