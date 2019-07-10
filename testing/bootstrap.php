<?php

$testDir = getcwd();

if (!file_exists($testDir . '/phpunit.xml.dist')) {
    throw new Exception('You are not in a test directory');
}

/**
 * Our tests load global dependencies using coomposer
 */
if (!trait_exists(Google\Cloud\TestUtils\TestTrait::class)) {
    throw new Exception('You need to run "bash testing/composer.sh" to '
        . 'installed the required global packages. Be sure to run these tests '
        . 'using the global phpunit package (~/.composer/vendor/bin/phpunit)');
}

if (file_exists($testDir . '/composer.json')) {
    if (!file_exists($testDir . '/vendor/autoload.php')) {
        throw new Exception('You need to run "composer install" in your current directory');
    }
    require_once $testDir . '/vendor/autoload.php';
}
