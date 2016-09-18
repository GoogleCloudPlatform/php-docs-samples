<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Speech\TranscribeCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new TranscribeCommand());
$application->run();
