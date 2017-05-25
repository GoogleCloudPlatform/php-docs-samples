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

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$application->add(new Command('shots'))
    ->setDescription('Detect shot changes in video using '
        .'Google Cloud Video Intelligence API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds shot changes in a video using the 
Google Cloud Video Intelligence API.

    <info>php %command.full_name% gs://cloudmleap/video/next/fox-snatched.mp4</info>

Example:
    <info>php %command.full_name% gs://cloudmleap/video/next/fox-snatched.mp4</info>
annotation_results {
  input_uri: "\/cloudmleap\/video\/next\/fox-snatched.mp4"
  shot_annotations {
    start_time_offset: 41729
    end_time_offset: 1000984
  }
  shot_annotations {
    start_time_offset: 1042713
    end_time_offset: 6006032
  }
}
EOF
    )->addArgument(
        'uri',
        InputArgument::REQUIRED,
        'Uri pointing to a video.'
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $uri = $input->getArgument('uri');
        analyze_shots($uri);
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}
$application->run();
