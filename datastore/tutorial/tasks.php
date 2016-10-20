#!/usr/bin/env php
<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Google\Cloud\Samples\Datastore\Tasks\CreateTaskCommand;
use Google\Cloud\Samples\Datastore\Tasks\DeleteTaskCommand;
use Google\Cloud\Samples\Datastore\Tasks\ListTasksCommand;
use Google\Cloud\Samples\Datastore\Tasks\MarkTaskDoneCommand;

$application = new Application();
$application->setName('Cloud Datastore sample cli');
$application->add(new CreateTaskCommand());
$application->add(new DeleteTaskCommand());
$application->add(new ListTasksCommand());
$application->add(new MarkTaskDoneCommand());
$application->run();
