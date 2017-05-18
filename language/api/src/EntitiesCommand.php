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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility for the Natural Language APIs.
 *
 * Usage: php language.php entities TEXT
 */
class EntitiesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('entities')
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
  -
    name: walk
    type: EVENT
    metadata: {  }
    salience: 0.3247361
    mentions:
      -
        text:
          content: walk
          beginOffset: 12
        type: COMMON
language: en
EOF
            )
            ->addArgument(
                'content',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Text or path to Cloud Storage file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = implode(' ', (array) $input->getArgument('content'));
        // Regex to match a Cloud Storage path as the first argument
        // e.g "gs://my-bucket/file_with_text.txt"
        if (preg_match('/^gs:\/\/([a-z0-9\._\-]+)\/(\S+)$/', $content, $matches)) {
            $result = analyze_entities_from_file($matches[1], $matches[2]);
        } else {
            $result = analyze_entities($content);
        }
        $output->write(annotation_to_string($result));
    }
}
