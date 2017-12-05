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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$application = new Application('Google Cloud Translate API');

// Add Detect Language command
$application->add(new Command('detect-language'))
    ->setDescription('Detect which language text was written in using Google Cloud Translate API')
    ->addArgument('text', InputArgument::REQUIRED, 'The text to examine.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects which language text was written in using the Google Cloud Translate API.

    <info>php %command.full_name% "Your text here"</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $text = $input->getArgument('text');
        require __DIR__ . '/src/detect_language.php';
    });

// Add List Codes command
$application->add(new Command('list-codes'))
    ->setDescription('List all the language codes in the Google Cloud Translate API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists all the language codes in the Google Cloud Translate API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        require __DIR__ . '/src/list_codes.php';
    });

// Add List Languages command
$application->add(new Command('list-langs'))
    ->setDescription('List language codes and names in the Google Cloud Translate API')
    ->addOption('target-language', 't', InputOption::VALUE_REQUIRED,
        'The ISO 639-1 code of language to use when printing names, eg. \'en\'.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> lists language codes and names in the Google Cloud Translate API.

    <info>php %command.full_name% -t en</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $targetLanguage = $input->getOption('target-language');
        require __DIR__ . '/src/list_languages.php';
    });

// Add Translate command
$application->add(new Command('translate'))
    ->setDescription('Translate text using Google Cloud Translate API')
    ->addArgument('text', InputArgument::REQUIRED, 'The text to translate.')
    ->addOption('model', null, InputOption::VALUE_REQUIRED, 'The model to use, "base" for standard and "nmt" for premium.')
    ->addOption('target-language', 't', InputOption::VALUE_REQUIRED,
        'The ISO 639-1 code of language to use when printing names, eg. \'en\'.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio using the Google Cloud Translate API.

    <info>php %command.full_name% -t ja "Hello World."</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $text = $input->getArgument('text');
        $targetLanguage = $input->getOption('target-language');
        $model = $input->getOption('model');
        if ($model) {
            require __DIR__ . '/src/translate_with_model.php';
        } else {
            require __DIR__ . '/src/translate.php';
        }
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
