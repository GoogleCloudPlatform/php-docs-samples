<?php

/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Translate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to translate.
 */
class TranslateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('translate')
            ->setDescription('Translate text using Google Cloud Translate API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command transcribes audio using the Google Cloud Translate API.

    <info>php %command.full_name% -k YOUR-API-KEY -t ja "Hello World."</info>

EOF
            )
            ->addOption(
                'api-key',
                'k',
                InputOption::VALUE_REQUIRED,
                'Your API key.'
            )
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'The text to translate.'
            )
            ->addOption(
                'target-language',
                't',
                InputOption::VALUE_REQUIRED,
                'The ISO 639-1 code of language to translate to, eg. \'en\'.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apiKey = $input->getOption('api-key');
        $text = $input->getArgument('text');
        $targetLanguage = $input->getOption('target-language');
        require(__DIR__ . '/snippets/translate.php');
    }
}
