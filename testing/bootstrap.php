<?php

$testDir = getcwd();

if (!file_exists($testDir . '/phpunit.xml.dist')) {
    throw new Exception('You are not in a sample directory');
}

if (file_exists($testDir . '/composer.json')) {
    if (!file_exists($testDir . '/vendor/autoload.php')) {
        throw new Exception('You need to run "composer install" in your current directory');
    }
    require_once $testDir . '/vendor/autoload.php';
}

/**
 * Load shared dependencies.
 * @see testing/composer.json
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception('You need to run "composer install -d testing/" from '
        . 'project root before running "phpunit" to run the samples tests.');
}

// Make sure that while testing we bypass the `final` keyword for the GAPIC client.
DG\BypassFinals::allowPaths([
    '*/src/V*/Client/*',
]);

DG\BypassFinals::enable();

require_once __DIR__ . '/vendor/autoload.php';
