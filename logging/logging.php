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

use Google\Cloud\Samples\Logging\CreateSinkCommand;
use Google\Cloud\Samples\Logging\DeleteLoggerCommand;
use Google\Cloud\Samples\Logging\DeleteSinkCommand;
use Google\Cloud\Samples\Logging\ListEntriesCommand;
use Google\Cloud\Samples\Logging\ListSinksCommand;
use Google\Cloud\Samples\Logging\UpdateSinkCommand;
use Google\Cloud\Samples\Logging\WriteCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new CreateSinkCommand());
$application->add(new DeleteLoggerCommand());
$application->add(new DeleteSinkCommand());
$application->add(new ListEntriesCommand());
$application->add(new ListSinksCommand());
$application->add(new UpdateSinkCommand());
$application->add(new WriteCommand());
$application->run();
