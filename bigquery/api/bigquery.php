<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\BigQuery\BrowseTableCommand;
use Google\Cloud\Samples\BigQuery\CopyTableCommand;
use Google\Cloud\Samples\BigQuery\DatasetsCommand;
use Google\Cloud\Samples\BigQuery\ExtractCommand;
use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Google\Cloud\Samples\BigQuery\TablesCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BrowseTableCommand());
$application->add(new CopyTableCommand());
$application->add(new DatasetsCommand());
$application->add(new ExtractCommand());
$application->add(new ImportCommand());
$application->add(new QueryCommand());
$application->add(new SchemaCommand());
$application->add(new TablesCommand());
$application->run();
