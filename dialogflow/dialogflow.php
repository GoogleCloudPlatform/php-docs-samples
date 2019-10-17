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

// list entity type command
$application->add((new Command('entity-type-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->setDescription('List entity types.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists entity types.

    <info>php %command.full_name% PROJECT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        entity_type_list($projectId);
    })
);

// create entity type command
$application->add((new Command('entity-type-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('display-name', InputArgument::REQUIRED,
        'Display name of the entity.')
    ->addOption('kind', 'k', InputOption::VALUE_REQUIRED,
        'Kind of entity. KIND_MAP (default) or KIND_LIST', EntityType_Kind::KIND_MAP)
    ->setDescription('Create entity types with provided display name.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates entity type with provided name.

    <info>php %command.full_name% PROJECT_ID DISPLAY_NAME -k KIND</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $displayName = $input->getArgument('display-name');
        $kind = $input->getOption('kind');
        entity_type_create($projectId, $displayName, $kind);
    })
);

// delete entity type command
$application->add((new Command('entity-type-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('entity-type-id', InputArgument::REQUIRED, 'ID of entity type.')
    ->setDescription('Delete entity types of provided entity type id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes entity type of provided id.

    <info>php %command.full_name% PROJECT_ID ENTITY_TYPE_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $entityTypeId = $input->getArgument('entity-type-id');
        entity_type_delete($projectId, $entityTypeId);
    })
);

// list entity command
$application->add((new Command('entity-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('entity-type-id', InputArgument::REQUIRED, 'ID of entity type.')
    ->setDescription('List entities of provided entity type id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists entities of provided entity type id.

    <info>php %command.full_name% PROJECT_ID ENTITY_TYPE_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $entityTypeId = $input->getArgument('entity-type-id');
        entity_list($projectId, $entityTypeId);
    })
);

// create entity command
$application->add((new Command('entity-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('entity-type-id', InputArgument::REQUIRED, 'ID of entity type.')
    ->addArgument('entity-value', InputArgument::REQUIRED, 'Value of the entity.')
    ->addArgument('synonyms', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
        'Synonyms that will map to provided entity value.')
    ->setDescription('Create entity value for entity type id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates entity value for entity type id.

    <info>php %command.full_name% PROJECT_ID ENTITY_TYPE_ID ENTITY_VALUE [synonyms ...]</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $entityTypeId = $input->getArgument('entity-type-id');
        $entityValue = $input->getArgument('entity-value');
        $synonyms = $input->getArgument('synonyms');
        entity_create($projectId, $entityTypeId, $entityValue, $synonyms);
    })
);

// delete entity command
$application->add((new Command('entity-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addArgument('entity-type-id', InputArgument::REQUIRED, 'ID of entity type.')
    ->addArgument('entity-value', InputArgument::REQUIRED, 'Value of the entity.')
    ->setDescription('Delete entity value from entity type id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes entity value from entity type id.

    <info>php %command.full_name% PROJECT_ID ENTITY_TYPE_ID ENTITY_VALUE</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $entityTypeId = $input->getArgument('entity-type-id');
        $entityValue = $input->getArgument('entity-value');
        entity_delete($projectId, $entityTypeId, $entityValue);
    })
);

// list context command
$application->add((new Command('context-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->setDescription('List contexts.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists contexts.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        context_list($projectId, $sessionId);
    })
);

// create context command
$application->add((new Command('context-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->addArgument('context-id', InputArgument::REQUIRED, 'ID of the context.')
    ->addOption('lifespan-count', 'c', InputOption::VALUE_REQUIRED,
        'Lifespan count of the context. Defaults to 1.', 1)
    ->setDescription('Create context of provided context id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates context of provided context id.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID CONTEXT_ID
    -c LIFESPAN_COUNT</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $contextId = $input->getArgument('context-id');
        $lifespan = $input->getOption('lifespan-count');
        context_create($projectId, $contextId, $sessionId, $lifespan);
    })
);

// delete context command
$application->add((new Command('context-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->addArgument('context-id', InputArgument::REQUIRED, 'ID of the context.')
    ->setDescription('Delete context of provided context id.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes context of provided context id.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID CONTEXT_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $contextId = $input->getArgument('context-id');
        context_delete($projectId, $contextId, $sessionId);
    })
);

// list session entity type command
$application->add((new Command('session-entity-type-list'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->setDescription('List session entity types.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists session entity types.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        session_entity_type_list($projectId, $sessionId);
    })
);

// create session entity type command
$application->add((new Command('session-entity-type-create'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->addArgument('entity-type-display-name', InputArgument::REQUIRED,
        'Display name of the entity type.')
    ->addArgument('entity-values', InputArgument::IS_ARRAY |
        InputArgument::REQUIRED, 'Entity values of the session entity type.')
    ->addOption('entity-override-mode', 'o', InputOption::VALUE_REQUIRED,
        'ENTITY_OVERRIDE_MODE_OVERRIDE (default) or ENTITY_OVERRIDE_MODE_SUPPLEMENT',
        SessionEntityType_EntityOverrideMode::ENTITY_OVERRIDE_MODE_OVERRIDE)
    ->setDescription('Create session entity type.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates session entity type with
display name and values provided.

    <info>php %command.full_name% PROJECT_ID -s SESSION_ID
    ENTITY_TYPE_DISPLAY_NAME entity_value [entity_values ...]
    -o ENTITY_OVERRIDE_MODE</info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $displayName = $input->getArgument('entity-type-display-name');
        $values = $input->getArgument('entity-values');
        $overrideMode = $input->getOption('entity-override-mode');
        session_entity_type_create($projectId, $displayName, $values,
            $sessionId, $overrideMode);
    })
);

// delete session entity type command
$application->add((new Command('session-entity-type-delete'))
    ->addArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
    ->addOption('session-id', 's', InputOption::VALUE_REQUIRED,
        'Identifier of the DetectIntent session.')
    ->addArgument('entity-type-display-name', InputArgument::REQUIRED,
        'Display name of the entity type.')
    ->setDescription('Delete session entity type of provided display name.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes specified session entity type.

    <info>php %command.full_name% PROJECT_ID SESSION_ID
     ENTITY_TYPE_DISPLAY_NAME </info>
EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project-id');
        $sessionId = $input->getOption('session-id');
        $displayName = $input->getArgument('entity-type-display-name');
        session_entity_type_delete($projectId, $displayName, $sessionId);
    })
);

if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}
$application->run();
