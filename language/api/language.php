<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Language\EntitiesCommand;
use Google\Cloud\Samples\Language\EverythingCommand;
use Google\Cloud\Samples\Language\SentimentCommand;
use Google\Cloud\Samples\Language\SyntaxCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new EntitiesCommand());
$application->add(new EverythingCommand());
$application->add(new SentimentCommand());
$application->add(new SyntaxCommand());
$application->run();
