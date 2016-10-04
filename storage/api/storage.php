<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Storage\ObjectAclCommand;
use Google\Cloud\Samples\Storage\BucketAclCommand;
use Google\Cloud\Samples\Storage\BucketDefaultAclCommand;
use Google\Cloud\Samples\Storage\BucketsCommand;
use Google\Cloud\Samples\Storage\EncryptionCommand;
use Google\Cloud\Samples\Storage\ObjectsCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BucketAclCommand());
$application->add(new BucketDefaultAclCommand());
$application->add(new BucketsCommand());
$application->add(new EncryptionCommand());
$application->add(new ObjectAclCommand());
$application->add(new ObjectsCommand());
$application->run();
