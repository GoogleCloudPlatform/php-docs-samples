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
namespace Google\Cloud\Samples\Speech;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$inputDefinition = new InputDefinition([
    new InputArgument('audio-file', InputArgument::REQUIRED, 'The audio file to transcribe'),
    new InputOption('encoding', null, InputOption::VALUE_REQUIRED,
        'The encoding of the audio file. This is required if the encoding is ' .
        'unable to be determined. '
    ),
    new InputOption('language-code', null, InputOption::VALUE_REQUIRED,
        'The language code for the language used in the source file. ',
        'en-US'
    ),
    new InputOption('sample-rate', null, InputOption::VALUE_REQUIRED,
        'The sample rate of the audio file in hertz. This is required ' .
        'if the sample rate is unable to be determined. '
    ),
    new InputOption('sample-rate', null, InputOption::VALUE_REQUIRED,
        'The sample rate of the audio file in hertz. This is required ' .
        'if the sample rate is unable to be determined. '
    ),
]);

$application = new Application('Cloud Speech');
$application->add(new Command('transcribe'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe an audio file using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a file using the
Google Cloud Speech API.

<info>php %command.full_name% audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        transcribe_sync($audioFile, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-gcs'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe audio from a Storage Object using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a Cloud Storage
Object using the Google Cloud Speech API.

<info>php %command.full_name% gs://my-bucket/audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        if (!preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $audioFile, $matches)) {
            throw new \Exception('Invalid file name. Must be gs://[bucket]/[audiofile]');
        }
        list($bucketName, $objectName) = array_slice($matches, 1);
        transcribe_sync_gcs($bucketName, $objectName, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-words'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe an audio file and print word time offsets using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a file using the
Google Cloud Speech API and prints word time offsets.

<info>php %command.full_name% audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        transcribe_sync_words($audioFile, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-async'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe an audio file asynchronously using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a file using the
Google Cloud Speech API asynchronously.

<info>php %command.full_name% audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        transcribe_async($audioFile, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-async-gcs'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe audio asynchronously from a Storage Object using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a Cloud Storage
object asynchronously using the Google Cloud Speech API.

<info>php %command.full_name% gs://my-bucket/audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        if (!preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $audioFile, $matches)) {
            throw new \Exception('Invalid file name. Must be gs://[bucket]/[audiofile]');
        }
        list($bucketName, $objectName) = array_slice($matches, 1);
        transcribe_async_gcs($bucketName, $objectName, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-async-words'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe an audio file asynchronously and print word time offsets using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a file using the
Google Cloud Speech API asynchronously and prints word time offsets.

<info>php %command.full_name% audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $audioFile = $input->getArgument('audio-file');
        $languageCode = $input->getOption('language-code');
        transcribe_async_words($audioFile, $languageCode, [
            'encoding' => $input->getOption('encoding'),
            'sampleRateHertz' => $input->getOption('sample-rate'),
        ]);
    });

$application->add(new Command('transcribe-stream'))
    ->setDefinition($inputDefinition)
    ->setDescription('Transcribe a stream of audio using Google Cloud Speech API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio from a stream using
the Google Cloud Speech API.

<info>php %command.full_name% audio_file.wav</info>

EOF
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        streaming_recognize(
            $input->getArgument('audio-file'),
            $input->getOption('language-code'),
            $input->getOption('encoding'),
            $input->getOption('sample-rate')
        );
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
