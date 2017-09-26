#!/usr/bin/env php
<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Samples\ErrorReporting;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Stackdriver Error Reporting');

$inputDefinition = new InputDefinition([
    new InputArgument('project_id', InputArgument::REQUIRED, 'The project id'),
    new InputArgument('message', InputArgument::OPTIONAL, 'The message to log id', 'My Error Message'),
    new InputOption('user', '', InputOption::VALUE_REQUIRED, 'The user attributed to the error.', 'test@user.com'),
]);

$application->add(new Command('manual'))
    ->setDefinition($inputDefinition)
    ->setDescription('Logs an error message with context data.')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project_id');
        $message = $input->getArgument('message');
        $user = $input->getOption('user');
        report_error_manually($projectId, $message, $user);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
