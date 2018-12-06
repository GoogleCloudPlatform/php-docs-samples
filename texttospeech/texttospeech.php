<?php
/**
 * Copyright 2018 Google Inc.
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
namespace Google\Cloud\Samples\TextToSpeech;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

$application = new Application('Cloud TTS');

$inputDefinition = new InputDefinition([
    new InputArgument('text', InputArgument::REQUIRED,
        'Text/SSML to synthesize.')
]);

$inputDefinitionFile = new InputDefinition([
    new InputArgument('path', InputArgument::REQUIRED, 'File to synthesize.')
]);


// [START tts_list_voices]
$application->add(new Command('list-voices'))
    ->setDescription('List the available voices')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists the available voices in
Google Cloud Text-to-Speech API.
<info>php %command.full_name% </info>
EOF
    )
    ->setCode(function () {
        list_voices();
    }
);
// [END tts_list_voices]

// [START tts_synthesize_ssml]
$application->add((new Command('synthesize-ssml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Synthesizes speech from the input string of ssml')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command synthesizes speech from the input string 
of ssml using Google Cloud Text-to-Speech API.
    <info>php %command.full_name% "<speak>Hello there.</speak>"</info>
EOF
    )
    ->setCode(function ($input) {
        $ssml = $input->getArgument('text');
        synthesize_ssml($ssml);
    })
);
// [END tts_synthesize_ssml]

// [START tts_synthesize_text]
$application->add((new Command('synthesize-text'))
    ->setDefinition($inputDefinition)
    ->setDescription('Synthesizes speech from the input string of text')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command synthesizes speech from the input string 
of text using Google Cloud Text-to-Speech API.
    <info>php %command.full_name% "hello there"</info>
EOF
    )
    ->setCode(function ($input) {
        $text = $input->getArgument('text');
        synthesize_text($text);
    })
);
// [END tts_synthesize_text]

// [START tts_synthesize_ssml_file]
$application->add((new Command('synthesize-ssml-file'))
    ->setDefinition($inputDefinitionFile)
    ->setDescription('Synthesizes speech from the input file of ssml')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command synthesizes speech from the input file 
of ssml using Google Cloud Text-to-Speech API.
    <info>php %command.full_name% path/to/file.ssml</info>
EOF
    )
    ->setCode(function ($input) {
        $path = $input->getArgument('path');
        synthesize_ssml_file($path);
    })
);
// [END tts_synthesize_ssml_file]

// [START tts_synthesize_text_file]
$application->add((new Command('synthesize-text-file'))
    ->setDefinition($inputDefinitionFile)
    ->setDescription('Synthesizes speech from the input file of text')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command synthesizes speech from the input file 
of text using Google Cloud Text-to-Speech API.
    <info>php %command.full_name% path/to/file.txt</info>
EOF
    )
    ->setCode(function ($input) {
        $path = $input->getArgument('path');
        synthesize_text_file($path);
    })
);
// [END tts_synthesize_text_file]

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
