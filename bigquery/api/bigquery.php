<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\BigQuery\BrowseTableCommand;
use Google\Cloud\Samples\BigQuery\DatasetsCommand;
use Google\Cloud\Samples\BigQuery\ExportCommand;
use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\ProjectsCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Google\Cloud\Samples\BigQuery\TablesCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BrowseTableCommand());
$application->add(new DatasetsCommand());
$application->add(new ExportCommand());
$application->add(new ImportCommand());
$application->add(new ProjectsCommand());
$application->add(new QueryCommand());
$application->add(new SchemaCommand());
$application->add(new TablesCommand());
$application->run();
