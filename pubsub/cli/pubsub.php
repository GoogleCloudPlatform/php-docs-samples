<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\PubSub\SubscriptionCommand;
use Google\Cloud\Samples\PubSub\TopicCommand;
use Google\Cloud\Samples\PubSub\IamCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new SubscriptionCommand());
$application->add(new TopicCommand());
$application->add(new IamCommand());
$application->run();
