<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\Firestore;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Firestore');

$inputDefinition = new InputDefinition([
    new InputOption('project', 'p', InputOption::VALUE_OPTIONAL, 'Your Google Cloud Project ID'),
]);

// Initialize command
$application->add((new Command('initialize'))
	->setDefinition($inputDefinition)
    ->setDescription('Initialize Cloud Firestore with default project ID.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command initializes Cloud Firestore using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	$projectId = $input->getOption('project');
    	if ($projectId) {
            fs_initialize_project_id($projectId);
    	} else {
    		fs_initialize();
    	}
    })
);

// Add Data #1 command
$application->add((new Command('add-data-1'))
	->setDefinition($inputDefinition)
    ->setDescription('Add data to a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds data to a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_data_1();
    })
);

// Add Data #2 command
$application->add((new Command('add-data-2'))
	->setDefinition($inputDefinition)
    ->setDescription('Add data to a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds data to a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_data_2();
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
