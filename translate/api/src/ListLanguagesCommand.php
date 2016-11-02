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

// [START translate_list_language_names]
use Google\Cloud\Translate\TranslateClient;
// [END translate_list_language_names]
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to translate.
 */
class ListLanguagesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('list-langs')
            ->setDescription('List language codes and names in the '
                . 'Google Cloud Translate API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> lists language codes and names in the Google Cloud Translate API.

    <info>php %command.full_name% -k YOUR-API-KEY -t en</info>

EOF
            )
            ->addOption(
                'api-key',
                'k',
                InputOption::VALUE_REQUIRED,
                'Your API key.'
            )
            ->addOption(
                'target-language',
                't',
                InputOption::VALUE_REQUIRED,
                'The ISO 639-1 code of language to use when printing names, eg. \'en\'.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->listLanguage(
            $input->getOption('api-key'),
            $input->getOption('target-language')
        );
    }

    // [START translate_list_language_names]
    /**
     * @param $apiKey string Your API key.
     * @param $targetLanguage string Language code: Print the names of the
     *   language in which language?
     */
    protected function listLanguage($apiKey, $targetLanguage)
    {
        $translate = new TranslateClient([
            'key' => $apiKey,
        ]);
        $result = $translate->localizedLanguages([
            'target' => $targetLanguage,
        ]);
        foreach ($result as $lang) {
            print("$lang[code]: $lang[name]\n");
        }
    }
    // [END translate_list_language_names]
}
