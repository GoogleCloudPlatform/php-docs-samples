<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Language\AnalyzeEntitiesCommand;
use Google\Cloud\Samples\Language\AnalyzeEverythingCommand;
use Google\Cloud\Samples\Language\AnalyzeSentimentCommand;
use Google\Cloud\Samples\Language\AnalyzeSyntaxCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AnalyzeEntitiesCommand());
$application->add(new AnalyzeEverythingCommand());
$application->add(new AnalyzeSentimentCommand());
$application->add(new AnalyzeSyntaxCommand());
$application->run();
