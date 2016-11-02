<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Google\Cloud\Samples\Translate\TranslateCommand());
$application->add(new Google\Cloud\Samples\Translate\DetectLanguageCommand());
$application->add(new Google\Cloud\Samples\Translate\ListCodesCommand());
$application->add(new Google\Cloud\Samples\Translate\ListLanguagesCommand());
$application->run();
