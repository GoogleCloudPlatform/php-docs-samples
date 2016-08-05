<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Symfony\Component\Console\Application;

$import = new ImportCommand();
$schema = new SchemaCommand();
$query = new QueryCommand();
$application = new Application();
$application->add($import);
$application->add($schema);
$application->add($query);
$application->run();
