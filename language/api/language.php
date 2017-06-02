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

namespace Google\Cloud\Samples\Language;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Yaml\Yaml;

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
    <info>php %command.full_name% "John took a walk."</info>
sentences:
  -
    text:
      content: 'John took a walk.'
      beginOffset: 0
    sentiment:
      magnitude: 0
      score: 0
tokens:
  -
    text:
      content: John
      beginOffset: 0
    partOfSpeech:
      tag: NOUN
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      ...
    dependencyEdge:
      headTokenIndex: 1
      label: NSUBJ
    lemma: John
    ...
entities:
  -
    name: John
    type: PERSON
    metadata: {  }
    salience: 0.67526394
    mentions:
      -
        text:
          content: John
          beginOffset: 0
        type: PROPER
      ...
documentSentiment:
  magnitude: 0
  score: 0
language: en
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $annotation = analyze_all_from_file($matches[1], $matches[2], $projectId);
        } else {
            $annotation = analyze_all($content, $projectId);
        }
        $output->write(Yaml::dump($annotation->info(), 6));
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
    <info>php %command.full_name% "John took a walk."</info>
entities:
  -
    name: John
    type: PERSON
    metadata: {  }
    salience: 0.67526394
    mentions:
      -
        text:
          content: John
          beginOffset: 0
        type: PROPER
  ...
language: en
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $annotation = analyze_entities_from_file($matches[1], $matches[2], $projectId);
        } else {
            $annotation = analyze_entities($content, $projectId);
        }
        $output->write(Yaml::dump($annotation->info(), 6));
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
    <info>php %command.full_name% "John took a walk."</info>
documentSentiment:
  magnitude: 0
  score: 0
language: en
sentences:
  -
    text:
      content: 'John took a walk.'
      beginOffset: 0
    sentiment:
      magnitude: 0
      score: 0
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $annotation = analyze_sentiment_from_file($matches[1], $matches[2], $projectId);
        } else {
            $annotation = analyze_sentiment($content, $projectId);
        }
        $output->write(Yaml::dump($annotation->info(), 6));
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
    <info>php %command.full_name% "John took a walk."</info>
sentences:
  -
    text:
      content: 'John took a walk.'
      beginOffset: 0
tokens:
  -
    text:
      content: John
      beginOffset: 0
    partOfSpeech:
      tag: NOUN
      aspect: ASPECT_UNKNOWN
      case: CASE_UNKNOWN
      form: FORM_UNKNOWN
      ...
    dependencyEdge:
      headTokenIndex: 1
      label: NSUBJ
    lemma: John
  -
    ...
language: en
entities: {  }
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getOption('project');
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $annotation = analyze_syntax_from_file($matches[1], $matches[2], $projectId);
        } else {
            $annotation = analyze_syntax($content, $projectId);
        }
        $output->write(Yaml::dump($annotation->info(), 6));
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
