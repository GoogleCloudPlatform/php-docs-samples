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
namespace Google\Cloud\Samples\Video;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

$application = new Application('Cloud Video Intelligence');

$inputDefinition = new InputDefinition([
    new InputArgument(
        'uri',
        InputArgument::REQUIRED,
        'Google Cloud Storage URI pointing to a video.'
    ),
    new InputOption(
        'polling-interval-seconds',
        '',
        InputOption::VALUE_REQUIRED,
        'Polling interval in seconds to waiting for a Video API response.'
    ),
]);

$application->add(new Command('labels'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect labels in video using the Video Intelligence API')
    ->setCode(function ($input, $output) {
        analyze_labels(
            $input->getArgument('uri'),
            ['pollingIntervalSeconds' => $input->getOption('polling-interval-seconds')]
        );
    });

$application->add(new Command('labels-in-file'))
    ->addArgument('file', InputArgument::REQUIRED,
        'Path to a local video file.')
    ->addOption('polling-interval-seconds', '', InputOption::VALUE_REQUIRED,
        'Polling interval in seconds to waiting for a Video API response.')
    ->setDescription('Detect labels in a file using the Video Intelligence API')
    ->setCode(function ($input, $output) {
        analyze_labels_file(
            $input->getArgument('file'),
            ['pollingIntervalSeconds' => $input->getOption('polling-interval-seconds')]
        );
    });

$application->add(new Command('explicit-content'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect explicit content in video using the Video Intelligence API')
    ->setCode(function ($input, $output) {
        analyze_explicit_content(
            $input->getArgument('uri'),
            ['pollingIntervalSeconds' => $input->getOption('polling-interval-seconds')]
        );
    });

$application->add(new Command('shots'))
    ->setDefinition($inputDefinition)
    ->setDescription('Detect shots in video using the Video Intelligence API')
    ->setCode(function ($input, $output) {
        analyze_shots(
            $input->getArgument('uri'),
            ['pollingIntervalSeconds' => $input->getOption('polling-interval-seconds')]
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
