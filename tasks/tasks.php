<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Samples\Tasks;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Tasks');

$inputDefinition = new InputDefinition([
    new InputArgument('project', InputArgument::REQUIRED, 'Project of the queue to add the task to.'),
    new InputArgument('queue', InputArgument::REQUIRED, 'ID (short name) of the queue to add the task to.'),
    new InputArgument('location', InputArgument::REQUIRED, 'Location of the queue to add the task to.'),
    new InputOption('payload', 'helloworld', InputOption::VALUE_OPTIONAL, 'Optional payload to attach to the App Engine HTTP request.'),
]);

// Create Task command
$application->add((new Command('create-task'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a task for a given queue with an arbitrary payload.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a task for a given Pull Queue.

    <info>php %command.full_name% PROJECT_ID QUEUE_ID LOCATION PAYLOAD</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $project = $input->getArgument('project');
        $queue = $input->getArgument('queue');
        $location = $input->getArgument('location');
        if ($payload = $input->getOption('payload')) {
            create_task($project, $queue, $location, $payload);
        } else {
            create_task($project, $queue, $location);
        }
    })
);

// Pull and Acknowledge Task command
$application->add((new Command('pull-and-acknowledge-task'))
    ->setDefinition($inputDefinition)
    ->setDescription('Pull and acknowledge a task from a given Pull Queue.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command pulls and acknowledges a task from a given Pull Queue.

    <info>php %command.full_name% PROJECT_ID QUEUE_ID LOCATION</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $project = $input->getArgument('project');
        $queue = $input->getArgument('queue');
        $location = $input->getArgument('location');
        $task = pull_task($project, $queue, $location);
        acknowledge_task($task);
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
