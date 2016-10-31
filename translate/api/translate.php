<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$apiKey = 'YOUR-API-KEY';

$application = new Application();
$application->add(new Google\Cloud\Samples\Translate\TranslateCommand($apiKey));
$application->add(new Google\Cloud\Samples\Translate\DetectLanguageCommand($apiKey));
$application->add(new Google\Cloud\Samples\Translate\ListCodesCommand($apiKey));
$application->add(new Google\Cloud\Samples\Translate\ListLanguagesCommand($apiKey));
$application->run();
