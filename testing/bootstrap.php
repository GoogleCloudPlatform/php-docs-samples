<?php

$testDir = getcwd();

if (!file_exists($testDir . '/phpunit.xml.dist')) {
    throw new Exception('You are not in a test directory');
}

if (file_exists($testDir . '/composer.json') && !file_exists($testDir . '/vendor/autoload.php')) {
    throw new Exception('You need to run "composer install" in the sample directory');
}

require_once $testDir .'/vendor/autoload.php';

