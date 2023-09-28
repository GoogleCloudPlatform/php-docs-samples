<?php

require __DIR__ . '/vendor/autoload.php';

if (count($argv) != 2) {
    die('Usage: check_version.php CONSTRAINT' . PHP_EOL);
}

if ('null' === $argv[1]) {
    // If there is no php constraint, it satisfies
    echo '0';
    return;
}

echo Composer\Semver\Semver::satisfies(PHP_VERSION, $argv[1]) ? '0' : '1';
