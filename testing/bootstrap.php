<?php

$testDir = getcwd();

if (!file_exists($testDir . '/phpunit.xml.dist')) {
    throw new Exception('You are not in a test directory');
}

if (file_exists($testDir . '/composer.json') && !file_exists($testDir . '/vendor/autoload.php')) {
    throw new Exception('You need to run "composer install" in your current directory');
}

if (!trait_exists(Google\Cloud\TestUtils\TestTrait::class)) {
    throw new Exception('You need to run "bash testing/composer.sh" to installed the required global packages');
}

require_once $testDir . '/vendor/autoload.php';
