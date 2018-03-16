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
use Symfony\Component\Console\Input\InputDefinition;

# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Dialogflow');

// base input definition
$inputDefinition = new InputDefinition([
    new InputArgument('project-id', InputArgument::REQUIRED,
        'Project/agent id. Required.')
]);

// input definition with session-id
$inputDefinitionSession = clone $inputDefinition;
$sessionIdOption = new InputOption('session-id', 's', InputOption::VALUE_REQUIRED, 
    'Identifier of the DetectIntent session. Defaults to random.');
$inputDefinitionSession->addOption($sessionIdOption);

// input definition for intent detection
$inputDefinitionDetect = clone $inputDefinitionSession;
$languageCodeOption = new InputOption('language-code', 'l', 
    InputOption::VALUE_REQUIRED, 'Language code of the query. Defaults to "en-US".');
$inputDefinitionDetect->addOption($languageCodeOption);

// input definition for text command
$inputDefinitionText = clone $inputDefinitionDetect;
$textArgument = new InputArgument('texts', InputArgument::IS_ARRAY | 
    InputArgument::REQUIRED, 'Text inputs.');
$inputDefinitionText->addArgument($textArgument);

// input definition for audio and stream commands
$inputDefinitionAudio = clone $inputDefinitionDetect;
$pathArgument = new InputArgument('path', InputArgument::REQUIRED, 
    'Path to audio file.');
$inputDefinitionAudio->addArgument($pathArgument);

// input definition for intent deletion command
$inputDefinitionIntentDelete = clone $inputDefinition;
$intentIdArgument = new InputArgument('intent-id', InputArgument::REQUIRED, 
    'ID of intent.');
$inputDefinitionIntentDelete->addArgument($intentIdArgument);

// input definition for intent creation command
$inputDefinitionIntentCreate = clone $inputDefinition;
$intentNameArgument = new InputArgument('display-name', InputArgument::REQUIRED,
    'Display name of intent.');
$inputDefinitionIntentCreate->addArgument($intentNameArgument);
$trainingPhrasesOption = new InputOption('training-phrases-parts', 't',
    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Training phrases.');
$inputDefinitionIntentCreate->addOption($trainingPhrasesOption);
$messageTextsOption = new InputOption('message-texts', 'm',
    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    'Message texts for the agent\'s response when the intent is detected.');
$inputDefinitionIntentCreate->addOption($messageTextsOption);

// input definition for entity type creation command
$inputDefinitionEntityTypeCreate = clone $inputDefinition;
$entityNameArgument = new InputArgument('display-name', 
    InputArgument::REQUIRED, 'Display name of the entity.');
$inputDefinitionEntityTypeCreate->addArgument($entityNameArgument);
$kindOption = new InputOption('kind', 'k', InputOption::VALUE_REQUIRED, 
    'Kind of entity. KIND_MAP (default) or KIND_LIST');
$inputDefinitionEntityTypeCreate->addOption($kindOption);

// input definition for entity type deletion command
$inputDefinitionEntityTypeDelete = clone $inputDefinition;
$entityTypeIdArgument = new InputArgument('entity-type-id', 
    InputArgument::REQUIRED, 'ID of entity type.');
$inputDefinitionEntityTypeDelete->addArgument($entityTypeIdArgument);

// input definition for entity commands
$inputDefinitionEntity = $inputDefinitionEntityTypeDelete;

// input definition for entity deletion command
$inputDefinitionEntityDelete = clone $inputDefinitionEntity;
$entityValueArgument = new InputArgument('entity-value', InputArgument::REQUIRED, 
    'Value of the entity.');
$inputDefinitionEntityDelete->addArgument($entityValueArgument);

// input definition for entity creation command
$inputDefinitionEntityCreate = clone $inputDefinitionEntityDelete;
$synonymsArgument = new InputArgument('synonyms', InputArgument::OPTIONAL | 
    InputArgument::IS_ARRAY, 'Synonyms that will map to provided entity value.');
$inputDefinitionEntityCreate->addArgument($synonymsArgument);

// input definition for context commands
$inputDefinitionContext = clone $inputDefinitionSession;
$contextArgument = new InputArgument('context-id', InputArgument::REQUIRED, 
    'ID of the context.');
$inputDefinitionContext->addArgument($contextArgument);

// input definition for context creation command
$inputDefinitionContextCreate = clone $inputDefinitionContext;
$lifespanCountOption = new InputOption('lifespan-count', 'c', 
    InputOption::VALUE_REQUIRED, 'Lifespan count of the context. Defaults to 1.');
$inputDefinitionContextCreate->addOption($lifespanCountOption);

// input definition for session entity type commands
$inputDefinitionSessionEntityType = clone $inputDefinitionSession;
$entityTypeNameArgument = new InputArgument('entity-type-display-name', 
    InputArgument::REQUIRED, 'Display name of the entity type.');
$inputDefinitionSessionEntityType->addArgument($entityTypeNameArgument);

// input definition for session entity type creation command
$inputDefinitionSessionEntityTypeCreate = clone $inputDefinitionSessionEntityType;
$sessionEntityValueArgument = new InputArgument('entity-values', InputArgument::IS_ARRAY | 
    InputArgument::REQUIRED, 'Entity values of the session entity type.');
$inputDefinitionSessionEntityTypeCreate->addArgument($sessionEntityValueArgument);
$entityOverrideOption = new InputOption('entity-override-mode', 'o', 
    InputOption::VALUE_REQUIRED, 'ENTITY_OVERRIDE_MODE_OVERRIDE (default) or 
    ENTITY_OVERRIDE_MODE_SUPPLEMENT');
$inputDefinitionSessionEntityTypeCreate->addOption($entityOverrideOption);


// detect text intent command
$application->add((new Command('detect-intent-texts'))
    ->setDefinition($inputDefinitionText)
    ->setDescription('Detect intent of text inputs using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided text 
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID -s SESSION-ID 
    -l LANGUAGE-CODE text [texts ...]</info>
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
    ->setDefinition($inputDefinitionAudio)
    ->setDescription('Detect intent of audio file using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided audio 
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID -s SESSION-ID 
    -l LANGUAGE-CODE AUDIO_FILE_PATH</info>
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
    ->setDefinition($inputDefinitionAudio)
    ->setDescription('Detect intent of audio stream using Dialogflow.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command detects the intent of provided text 
using Dialogflow.

    <info>php %command.full_name% PROJECT_ID -s SESSION-ID 
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
    ->setDefinition($inputDefinition)
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
    ->setDefinition($inputDefinitionIntentCreate)
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
    ->setDefinition($inputDefinitionIntentDelete)
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
    ->setDefinition($inputDefinition)
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
    ->setDefinition($inputDefinitionEntityTypeCreate)
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
    ->setDefinition($inputDefinitionEntityTypeDelete)
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
    ->setDefinition($inputDefinitionEntity)
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
    ->setDefinition($inputDefinitionEntityCreate)
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
    ->setDefinition($inputDefinitionEntityDelete)
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
    ->setDefinition($inputDefinitionSession)
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
    ->setDefinition($inputDefinitionContextCreate)
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
    ->setDefinition($inputDefinitionContext)
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
    ->setDefinition($inputDefinitionSession)
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
    ->setDefinition($inputDefinitionSessionEntityTypeCreate)
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
    ->setDefinition($inputDefinitionSessionEntityType)
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