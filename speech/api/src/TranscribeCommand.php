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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to transcribe.
 *
 * Usage: php speech.php transcribe
 */
class TranscribeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('transcribe')
            ->setDescription('Transcribe Audio using Google Cloud Speech API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio using the Google Cloud Speech API.

    <info>php %command.full_name% audio_file.wav</info>

EOF
            )
            ->addArgument(
                'audio-file',
                InputArgument::REQUIRED,
                'The audio file to transcribe'
            )
            ->addOption(
                'encoding',
                null,
                InputOption::VALUE_REQUIRED,
                'The encoding of the audio file. This is required if the encoding is ' .
                'unable to be determined. '
            )
            ->addOption(
                'language-code',
                null,
                InputOption::VALUE_REQUIRED,
                'The language code for the language used in the source file. ',
                'en-US'
            )
            ->addOption(
                'sample-rate',
                null,
                InputOption::VALUE_REQUIRED,
                'The sample rate of the audio file in hertz. This is required ' .
                'if the sample rate is unable to be determined. '
            )
            ->addOption(
                'sync',
                null,
                InputOption::VALUE_NONE,
                'Run the transcription synchronously. '
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encoding = $input->getOption('encoding');
        $languageCode = $input->getOption('language-code');
        $sampleRate = $input->getOption('sample-rate');
        $audioFile = $input->getArgument('audio-file');
        $options = [
            'encoding' => $encoding,
            'languageCode' => $languageCode,
            'sampleRateHertz' => $sampleRate,
        ];
        if ($isGcs = preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $audioFile, $matches)) {
            list($bucketName, $objectName) = array_slice($matches, 1);
        }
        if ($isGcs) {
            if ($input->getOption('sync')) {
                transcribe_sync_gcs($bucketName, $objectName, $languageCode, $options);
            } else {
                transcribe_async_gcs($bucketName, $objectName, $languageCode, $options);
            }
        } else {
            if ($input->getOption('sync')) {
                transcribe_sync($audioFile, $languageCode, $options);
            } else {
                transcribe_async($audioFile, $languageCode, $options);
            }
        }
    }
}
