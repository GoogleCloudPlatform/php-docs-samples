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

namespace Google\Cloud\Samples\Logging;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Logging');

$inputDefinition = new InputDefinition([
    new InputArgument(
        'project',
        InputArgument::REQUIRED,
        'The Google Cloud Platform project name to use for this command.'
    ),
    new InputOption(
        'logger',
        null,
        InputOption::VALUE_OPTIONAL,
        'The name of the logger. By naming a logger, you can logically treat '
        . 'log entries in a logger; e.g. you can list or delete all the log '
        . 'entries by the name of the logger.',
        'my_logger'
    )
]);

// Create Sink command
$application->add(new Command('create-sink'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Creates a Logging sink')
    ->addOption('sink',
        null,
        InputOption::VALUE_OPTIONAL,
        'The name of the Logging sink',
        'my_sink'
    )->addOption(
        'bucket',
        null,
        InputOption::VALUE_REQUIRED,
        'The destination bucket name'
    )->addOption(
        'filter',
        null,
        InputOption::VALUE_OPTIONAL,
        'The filter expression for the sink',
        ''
    )->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $sinkName = $input->getOption('sink');
        $loggerName = $input->getOption('logger');
        $filter = $input->getOption('filter');
        $bucketName = $input->getOption('bucket');
        $destination = sprintf(
            'storage.googleapis.com/%s',
            $bucketName
        );
        $loggerFullName = sprintf(
            'projects/%s/logs/%s',
            $projectId,
            $loggerName
        );
        $filterString = sprintf('logName = "%s"', $loggerFullName);
        if (!empty($filter)) {
            $filterString .= ' AND ' . $filter;
        }
        create_sink($projectId, $sinkName, $destination, $filterString);
    });

$application->add(new Command('delete-logger'))
    ->setDefinition($inputDefinition)
    ->setDescription('Deletes the given logger and its entries')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $loggerName = $input->getOption('logger');
        delete_logger($projectId, $loggerName);
    });

$application->add(new Command('delete-sink'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Deletes a Logging sink')
    ->addOption(
        'sink',
        null,
        InputOption::VALUE_OPTIONAL,
        'The name of the Logging sink',
        'my_sink'
    )->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $sinkName = $input->getOption('sink');
        delete_sink($projectId, $sinkName);
    });

$application->add(new Command('list-entries'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists log entries in the logger')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $loggerName = $input->getOption('logger');
        $entries = list_entries($projectId, $loggerName);
    });

$application->add(new Command('list-sinks'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists sinks')
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $sinks = list_sinks($projectId);
    });

$application->add(new Command('update-sink'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Updates a Logging sink')
    ->addOption(
        'sink',
        null,
        InputOption::VALUE_OPTIONAL,
        'The name of the Logging sink',
        'my_sink'
    )->addOption(
        'filter',
        null,
        InputOption::VALUE_OPTIONAL,
        'The filter expression for the sink',
        ''
    )->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $sinkName = $input->getOption('sink');
        $loggerName = $input->getOption('logger');
        $filter = $input->getOption('filter');
        $loggerFullName = sprintf(
            'projects/%s/logs/%s',
            $projectId,
            $loggerName
        );
        $filterString = sprintf('logName = "%s"', $loggerFullName);
        if (!empty($filter)) {
            $filterString .= ' AND ' . $filter;
        }
        update_sink($projectId, $sinkName, $filterString);
    });

$application->add(new Command('write'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Writes log entries to the given logger')
    ->addArgument(
        'message',
        InputArgument::OPTIONAL,
        'The log message to write',
        'Hello'
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $message = $input->getArgument('message');
        $loggerName = $input->getOption('logger');
        write_log($projectId, $loggerName, $message);
    });

$application->add(new Command('write-psr'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Writes log entries using a PSR logger')
    ->addArgument(
        'message',
        InputArgument::OPTIONAL,
        'The log message to write',
        'Hello'
    )
    ->addOption(
        'level',
        null,
        InputOption::VALUE_REQUIRED,
        'The log level for the PSR logger',
        \Psr\Log\LogLevel::WARNING
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $message = $input->getArgument('message');
        $loggerName = $input->getOption('logger');
        $level = $input->getOption('level');
        write_with_psr_logger($projectId, $loggerName, $message, $level);
    });

$application->add(new Command('write-monolog'))
    ->setDefinition(clone $inputDefinition)
    ->setDescription('Writes log entries using a Monolog logger')
    ->addArgument(
        'message',
        InputArgument::OPTIONAL,
        'The log message to write',
        'Hello'
    )
    ->addOption(
        'level',
        null,
        InputOption::VALUE_REQUIRED,
        'The log level for the PSR logger',
        \Psr\Log\LogLevel::WARNING
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $message = $input->getArgument('message');
        $loggerName = $input->getOption('logger');
        $level = $input->getOption('level');
        write_with_monolog_logger($projectId, $loggerName, $message, $level);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
