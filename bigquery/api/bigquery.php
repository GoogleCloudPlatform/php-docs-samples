<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\BigQuery\DatasetsCommand;
use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Symfony\Component\Console\Application;

$datasets = new DatasetsCommand();
$import = new ImportCommand();
$query = new QueryCommand();
$schema = new SchemaCommand();
$application = new Application();
$application->add($datasets);
$application->add($import);
$application->add($query);
$application->add($schema);
$application->run();
