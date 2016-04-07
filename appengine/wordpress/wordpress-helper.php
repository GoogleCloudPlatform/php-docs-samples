#!/usr/bin/env php
<?php
// wordpress-setup.php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Helper\WordPressSetup;
use Symfony\Component\Console\Application;

$command = new WordPressSetup();
$application = new Application();
$application->add($command);
$application->run();
