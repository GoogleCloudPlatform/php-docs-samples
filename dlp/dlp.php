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
namespace Google\Cloud\Samples\Dlp;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

$application = new Application('Cloud DLP');

$application->add(new Command('inspect-string'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to inspect')
    ->setDescription('Inspect a string using the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        inspect_string(
            $input->getArgument('string')
        );
    });

$application->add(new Command('inspect-file'))
    ->addArgument('path', InputArgument::REQUIRED, 'The file path to inspect')
    ->setDescription('Inspect a file using the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        inspect_file(
            $input->getArgument('path')
        );
    });

$application->add(new Command('inspect-datastore'))
    ->addArgument('kind', InputArgument::REQUIRED, 'The Datastore kind to inspect')
    ->addArgument('namespace', InputArgument::OPTIONAL, 'The Datastore Namespace ID to inspect')
    ->addArgument('project', InputArgument::OPTIONAL, 'The GCP Project ID for the Datastore call')
    ->setDescription('Inspect Cloud Datastore using the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        inspect_datastore(
            $input->getArgument('kind'),
            $input->getArgument('namespace'),
            $input->getArgument('project')
        );
    });

$application->add(new Command('list-info-types'))
    ->addArgument('category',
        InputArgument::OPTIONAL,
        'The category for the info types')
    ->addArgument('language-code', InputArgument::OPTIONAL, 'The text to inspect', '')
    ->setDescription('Lists all Info Types for the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        list_info_types(
            $input->getArgument('category'),
            $input->getArgument('language-code')
        );
    });

$application->add(new Command('list-categories'))
    ->addArgument('language-code', InputArgument::OPTIONAL, 'The text to inspect', '')
    ->setDescription('Lists all Info Type Categories for the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        list_categories(
            $input->getArgument('language-code')
        );
    });

$application->add(new Command('redact-string'))
    ->addArgument('string', InputArgument::REQUIRED, 'The text to inspect')
    ->addArgument('replace-string',
        InputArgument::OPTIONAL,
        'The text to replace the sensitive content with',
        'xxx')
    ->setDescription('Redact sensitive data from a string using the Data Loss Prevention (DLP) API.')
    ->setCode(function ($input, $output) {
        redact_string(
            $input->getArgument('string'),
            $input->getArgument('replace-string')
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
