<?php

include_once __DIR__ . '/../vendor/autoload.php';

use SelfUpdate\SelfUpdateCommand;
use Startwind\Inventorio\Command\CollectCommand;
use Startwind\Inventorio\Command\InitCommand;
use Symfony\Component\Console\Application;

const INVENTORIO_VERSION = '##INVENTORIO_VERSION##';
const INVENTORIO_NAME = 'Inventorio';

$application = new Application();

$application->setVersion(INVENTORIO_VERSION);
$application->setName(INVENTORIO_NAME);

$application->add(new CollectCommand());
$application->add(new InitCommand());

if (!str_contains(INVENTORIO_VERSION, '##INVENTORIO_VERSION')) {
    $application->add(new SelfUpdateCommand(INVENTORIO_NAME, INVENTORIO_VERSION, "startwind/inventorio-command-line"));
}

$application->run();
