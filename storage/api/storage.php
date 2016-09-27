<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Storage\AclCommand;
use Google\Cloud\Samples\Storage\BucketsCommand;
use Google\Cloud\Samples\Storage\EncryptionCommand;
use Google\Cloud\Samples\Storage\ObjectsCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AclCommand());
$application->add(new BucketsCommand());
$application->add(new EncryptionCommand());
$application->add(new ObjectsCommand());
$application->run();
