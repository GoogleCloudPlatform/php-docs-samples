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

namespace Google\Cloud\Samples\Language;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Natural Language');

$inputDefinition = new InputDefinition([
    new InputArgument(
        'content',
        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'Text or path to Cloud Storage file.'
    ),
    new InputOption('project', 'p', InputOption::VALUE_OPTIONAL, 'The project id'),
]);

// Analyze All command
$application->add((new Command('all'))
    ->setDefinition($inputDefinition)
    ->setDescription('Analyze syntax, sentiment and entities in text.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "Barack Obama lives in Washington D.C."</info>
Name: Barack Obama
Type: PERSON
Salience: 0.676553
Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama
Knowledge Graph MID: /m/02mjmr
Mentions:
  Begin Offset: 0
  Content: Barack Obama
  Mention Type: PROPER


Name: Washington D.C.
Type: LOCATION
Salience: 0.323447
Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.
Knowledge Graph MID: /m/0rh6k
Mentions:
  Begin Offset: 22
  Content: Washington D.C.
  Mention Type: PROPER
...
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            analyze_all_from_file($matches[1], $matches[2], $projectId);
        } else {
            analyze_all($content, $projectId);
        }
    })
);

// Analyze Entities command
$application->add((new Command('entities'))
    ->setDefinition($inputDefinition)
    ->setDescription('Analyze entities in text.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "Barack Obama lives in Washington D.C."</info>
Name: Barack Obama
Type: PERSON
Salience: 0.676553
Wikipedia URL: https://en.wikipedia.org/wiki/Barack_Obama
Knowledge Graph MID: /m/02mjmr
Mentions:
  Begin Offset: 0
  Content: Barack Obama
  Mention Type: PROPER


Name: Washington D.C.
Type: LOCATION
Salience: 0.323447
Wikipedia URL: https://en.wikipedia.org/wiki/Washington,_D.C.
Knowledge Graph MID: /m/0rh6k
Mentions:
  Begin Offset: 22
  Content: Washington D.C.
  Mention Type: PROPER
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            analyze_entities_from_file($matches[1], $matches[2], $projectId);
        } else {
            analyze_entities($content, $projectId);
        }
    })
);

// Analyze Sentiment command
$application->add((new Command('sentiment'))
    ->setDefinition($inputDefinition)
    ->setDescription('Analyze sentiment in text.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "I like burgers. I dislike fish."</info>
Document Sentiment:
  Magnitude: 1.3
  Score: 0

Sentence: I like burgers.
Sentence Sentiment:
  Magnitude: 0.6
  Score: 0.6

Sentence: I dislike fish.
Sentence Sentiment:
  Magnitude: 0.6
  Score: -0.6
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            analyze_sentiment_from_file($matches[1], $matches[2], $projectId);
        } else {
            analyze_sentiment($content, $projectId);
        }
    })
);

// Analyze Syntax command
$application->add((new Command('syntax'))
    ->setDefinition($inputDefinition)
    ->setDescription('Analyze syntax in text.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "Barack Obama lives in Washington D.C."</info>
Token text: Barack
Token part of speech: NOUN

Token text: Obama
Token part of speech: NOUN

Token text: lives
Token part of speech: VERB

Token text: in
Token part of speech: ADP

Token text: Washington
Token part of speech: NOUN

Token text: D.C.
Token part of speech: NOUN
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            analyze_syntax_from_file($matches[1], $matches[2], $projectId);
        } else {
            analyze_syntax($content, $projectId);
        }
    })
);

// Analyze Entity Sentiment command
$application->add((new Command('entity-sentiment'))
    ->setDefinition($inputDefinition)
    ->setDescription('Analyze entity sentiment in text.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command analyzes text using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to analyze.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "New York is great. New York is good."</info>
Entity Name: New York
Entity Type: LOCATION
Entity Salience: 1
Entity Magnitude: 1.7999999523163
Entity Score: 0

Mentions: 
  Begin Offset: 0
  Content: New York
  Mention Type: PROPER
  Mention Magnitude: 0.89999997615814
  Mention Score: 0.89999997615814

  Begin Offset: 17
  Content: New York
  Mention Type: PROPER
  Mention Magnitude: 0.80000001192093
  Mention Score: -0.80000001192093
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            analyze_entity_sentiment_from_file($content, $projectId);
        } else {
            analyze_entity_sentiment($content, $projectId);
        }
    })
);

// Classify Text command
$application->add((new Command('classify'))
    ->setDefinition($inputDefinition)
    ->setDescription('Classify text into categories.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command classifies text into categories using the Google Cloud Natural Language API.

    <info>php %command.full_name% Text to classify.</info>

    <info>php %command.full_name% gs://my_bucket/file_with_text.txt</info>

Example:
    <info>php %command.full_name% "The first two gubernatorial elections since President Donald Trump took office went in favor of Democratic candidates in Virginia and New Jersey."</info>
Category Name: /News/Politics
Confidence: 0.99000000953674

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            classify_text_from_file($content, $projectId);
        } else {
            classify_text($content, $projectId);
        }
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
