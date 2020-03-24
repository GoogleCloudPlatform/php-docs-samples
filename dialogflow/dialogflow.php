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

namespace Google\Cloud\Samples\Dialogflow;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Google\Cloud\Dialogflow\V2\EntityType_Kind;
use Google\Cloud\Dialogflow\V2\SessionEntityType_EntityOverrideMode;

# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Dialogflow');

// detect text intent command
$application->add((new Command('detect-intent-texts'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session. Defaults to random.')
    ->addOption('language-code', 'l', InputOption::VALUE_REQUIRED,
        'Language code of the query. Defaults to "en-US".', 'en-US')
    ->addArgument('texts', InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'Text inputs.')
    ->setDescription('Detect intent of text inputs using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided text
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID [-s SESSION_ID]
    [-l LANGUAGE-CODE] text [texts ...]</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $languageCode = $input->getOption('language-code');
        $texts = $input->getArgument('texts');
        detect_intent_texts($projectId, $texts, $sessionId, $languageCode);
    })
);

// detect audio intent command
$application->add((new Command('detect-intent-audio'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session. Defaults to random.')
    ->addOption('language-code', 'l', InputOption::VALUE_REQUIRED,
        'Language code of the query. Defaults to "en-US".', 'en-US')
    ->addArgument('path', InputArgument::REQUIRED, 'Path to audio file.')
    ->setDescription('Detect intent of audio file using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided audio
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID [-s SESSION_ID]
    [-l LANGUAGE-CODE] AUDIO_FILE_PATH</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $languageCode = $input->getOption('language-code');
        $path = $input->getArgument('path');
        detect_intent_audio($projectId, $path, $sessionId, $languageCode);
    })
);

// detect stream intent command
$application->add((new Command('detect-intent-stream'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session. Defaults to random.')
    ->addOption('language-code', 'l', InputOption::VALUE_REQUIRED,
        'Language code of the query. Defaults to "en-US".', 'en-US')
    ->addArgument('path', InputArgument::REQUIRED, 'Path to audio file.')
    ->setDescription('Detect intent of audio stream using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided text
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID
    -l LANGUAGE-CODE AUDIO_FILE_PATH</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $languageCode = $input->getOption('language-code');
        $path = $input->getArgument('path');
        detect_intent_stream($projectId, $path, $sessionId, $languageCode);
    })
);

// list intent command
$application->add((new Command('intent-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->setDescription('List intents.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists intents.

    <info>php %command.full_name% PROJECT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        intent_list($projectId);
    })
);

// create intent command
$application->add((new Command('intent-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('display-name', InputArgument::REQUIRED,
        'Display name of intent.')
    ->addOption('training-phrases-parts', 't', InputOption::VALUE_REQUIRED |
        InputOption::VALUE_IS_ARRAY, 'Training phrases.')
    ->addOption('message-texts', 'm',
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'Message texts for the agent\'s response when the intent is detected.')
    ->setDescription('Create intent of provided display name.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates intent of provided display name.

    <info>php %command.full_name% PROJECT_ID DISPLAY_NAME -t training_phrase_part
    [-t trainining_phrase_part ...] -m message_text [-m message_text ...] </info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $displayName = $input->getArgument('display-name');
        $traingPhrases = $input->getOption('training-phrases-parts');
        $messageTexts = $input->getOption('message-texts');
        intent_create($projectId, $displayName, $traingPhrases, $messageTexts);
    })
);

// delete intent command
$application->add((new Command('intent-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('intent-id', InputArgument::REQUIRED, 'ID of intent.')
    ->setDescription('Delete intent of provided intent id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes intent of provided intent id.

    <info>php %command.full_name% PROJECT_ID INTENT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $intentId = $input->getArgument('intent-id');
        intent_delete($projectId, $intentId);
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}
$application->run();
