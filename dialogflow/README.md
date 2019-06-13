# Dialogflow: PHP Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=dialogflow

## Description

This command-line application demonstrates how to invoke Dialogflow
API from PHP.

## Before you begin
1. Follow the first 2 steps of [this quickstart](https://cloud.google.com/dialogflow-enterprise/docs/quickstart-api).
Feel free to stop after you've created an agent.

2. This sample comes with a [sample agent](https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/dialogflow/resources/RoomReservation.zip) which you can use to try the samples with. Follow the instructions on [this page](https://dialogflow.com/docs/best-practices/import-export-for-versions) to import the agent from the [console](https://console.dialogflow.com/api-client).
> WARNING: Importing the sample agent will add intents and entities to your Dialogflow agent. You might want to use a different Google Cloud Platform Project, or export your Dialogflow agent before importing the sample agent to save a version of your agent before the sample agent was imported.

3.  Clone the repo and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/dialogflow
```

4. Follow [this guide](https://cloud.google.com/php/grpc) to install gRPC for PHP.

5.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

## Samples

```
usage: php dialogflow.php command [options] [arguments]
```

### Detect intent (texts)
```
DialogFlow API detect intent PHP sample with text inputs.

Usage:
  php dialogflow.php detect-intent-texts [options] <PROJECT_ID> <texts> (<texts>)...

Examples:
  php dialogflow.php detect-intent-texts -h
  php dialogflow.php detect-intent-texts PROJECT_ID "hello" "book a meeting room" "Mountain View"
  php dialogflow.php detect-intent-texts -s SESSION_ID PROJECT_ID "tomorrow" "10 AM" "2 hours" "10 people" "A" "yes"

Command:
  detect-intent-texts

Arguments:
  PROJECT_ID               project/agent id.
  texts                    array of text inputs separated by space.

Options:
  -s SESSION_ID            identifier of DetectIntent session. defaults to random.
  -l LANGUAGE_CODE         language code of the query. defaults to "en-US".

```

### Detect intent (audio)
```
DialogFlow API detect intent PHP sample with audio file.

Usage:
  php dialogflow.php detect-intent-audio [options] <PROJECT_ID> <AUDIO_FILE_PATH>

Examples:
  php dialogflow.php detect-intent-audio -h
  php dialogflow.php detect-intent-audio PROJECT_ID resources/book_a_room.wav
  php dialogflow.php detect-intent-audio -s SESSION_ID PROJECT_ID resources/mountain_view.wav

Command:
  detect-intent-audio

Arguments:
  PROJECT_ID               project/agent id.
  AUDIO_FILE_PATH          path to audio file.

Options:
  -s SESSION_ID            identifier of DetectIntent session. defaults to random.
  -l LANGUAGE_CODE         language code of the query. defaults to "en-US".

```

### Detect intent (streaming)
```
DialogFlow API detect intent PHP sample with audio file processed as an audio stream.

Usage:
  php dialogflow.php detect-intent-stream [options] <PROJECT_ID> <AUDIO_FILE_PATH>

Examples:
  php dialogflow.php detect-intent-stream -h
  php dialogflow.php detect-intent-stream PROJECT_ID resources/book_a_room.wav
  php dialogflow.php detect-intent-stream -s SESSION_ID PROJECT_ID resources/mountain_view.wav

Command:
  detect-intent-stream

Arguments:
  PROJECT_ID               project/agent id.
  AUDIO_FILE_PATH          path to audio file.

Options:
  -s SESSION_ID            id of DetectIntent session. defaults to random.
  -l LANGUAGE_CODE         language code of the query. defaults to "en-US".

```

### Context management
```
DialogFlow API PHP samples showing how to manage contexts.

Usage:
  php dialogflow.php context-list [options] <PROJECT_ID>
  php dialogflow.php context-create [options] <PROJECT_ID> <CONTEXT_ID>
  php dialogflow.php context-delete [options] <PROJECT_ID> <CONTEXT_ID>

Examples:
  php dialogflow.php context-create -h
  php dialogflow.php context-list -s SESSION_ID PROJECT_ID
  php dialogflow.php context-create -s SESSION_ID -l 2 PROJECT_ID test-context-id
  php dialogflow.php context-delete -s SESSION_ID PROJECT_ID test-context-id

Commands:
  session-entity-type-list
  session-entity-type-create
  session-entity-type-delete

Arguments:
  PROJECT_ID               project/agent id.
  CONTEXT_ID               id of context.

Options:
  -s SESSION_ID            id of DetectIntent session. required.
  -l LIFESPAN_COUNT        lifespan count of the context.

```

### Intent management
```
DialogFlow API PHP samples showing how to manage intents.

Usage:
  php dialogflow.php intent-list <PROJECT_ID>
  php dialogflow.php intent-create [options] <PROJECT_ID> <DISPLAY_NAME>
  php dialogflow.php intent-delete <PROJECT_ID> <INTENT_ID>

Examples:
  php dialogflow.php intent-create -h
  php dialogflow.php intent-list PROJECT_ID
  php dialogflow.php intent-create PROJECT_ID "room.cancellation - yes" -t "cancel" -m "are you sure you want to cancel?"
  php dialogflow.php intent-delete PROJECT_ID 74892d81-7901-496a-bb0a-c769eda5180e

Commands:
  intent-list
  intent-create
  intent-delete

Arguments:
  PROJECT_ID               project/agent id.
  DISPLAY_NAME             display name of intent.
  INTENT_ID                id of intent.

Options:
  -t training_phrase_part  training phrase.
  -m message_texts         message text for the agent's response when intent is detected.

```

### Entity type management
```
DialogFlow API PHP samples showing how to manage entity types.

Usage:
  php dialogflow.php entity-type-list <PROJECT_ID>
  php dialogflow.php entity-type-create [options] <PROJECT_ID> <ENTITY_TYPE_DISPLAY_NAME>
  php dialogflow.php entity-type-delete <PROJECT_ID> <ENTITY_TYPE_ID>

Examples:
  php dialogflow.php entity-type-create -h
  php dialogflow.php entity-type-list PROJECT_ID
  php dialogflow.php entity-type-create PROJECT_ID employee
  php dialogflow.php entity-type-delete PROJECT_ID e57238e2-e692-44ea-9216-6be1b2332e2a

Commands:
  entity-type-list
  entity-type-create
  entity-type-delete

Arguments:
  PROJECT_ID               project/agent id.
  ENTITY_TYPE_DISPLAY_NAME display name of entity type.
  ENTITY_TYPE_ID           id of entity type.

Option:
  -k KIND                  kind of entity. KIND_MAP (default) or KIND_LIST

```

### Entity management
```
DialogFlow API PHP samples showing how to manage entities.

Usage:
  php dialogflow.php entity-list <PROJECT_ID> <ENTITY_TYPE_ID>
  php dialogflow.php entity-create <PROJECT_ID> <ENTITY_TYPE_ID> <ENTITY_VALUE> [<synonyms>]...
  php dialogflow.php entity-delete <PROJECT_ID> <ENTITY_TYPE_ID> <ENTITY_VALUE>

Examples:
  php dialogflow.php entity-create -h
  php dialogflow.php entity-list PROJECT_ID e57238e2-e692-44ea-9216-6be1b2332e2a
  php dialogflow.php entity-create PROJECT_ID e57238e2-e692-44ea-9216-6be1b2332e2a new_room basement cellar
  php dialogflow.php entity-delete PROJECT_ID e57238e2-e692-44ea-9216-6be1b2332e2a new_room

Commands:
  entity-list
  entity-create
  entity-delete

Arguments:
  PROJECT_ID               project/agent id.
  ENTITY_TYPE_ID           id of entity type.
  ENTITY_VALUE             value of the entity.
  synonyms                 array of synonyms that will map to provided entity value.

```

### Session entity type management
```
DialogFlow API PHP samples showing how to manage session entity types.

Usage:
  php dialogflow.php session-entity-type-list [options] <PROJECT_ID>
  php dialogflow.php session-entity-type-create [options] <PROJECT_ID> <ENTITY_TYPE_DISPLAY_NAME> <entity_value> (<entity_value>)...
  php dialogflow.php session-entity-type-delete [options] <PROJECT_ID> <ENTITY_TYPE_DISPLAY_NAME>

Examples:
  php dialogflow.php session-entity-type-create -h
  php dialogflow.php session-entity-type-list -s SESSION_ID PROJECT_ID
  php dialogflow.php session-entity-type-create -s SESSION_ID PROJECT_ID room c d e f
  php dialogflow.php session-entity-type-delete -s SESSION_ID PROJECT_ID room

Commands:
  session-entity-type-list
  session-entity-type-create
  session-entity-type-delete

Arguments:
  PROJECT_ID               project/agent id.
  ENTITY_TYPE_DISPLAY_NAME display name of entity type.
  entity_value             array of entity values separated by space.

Options:
  -s SESSION_ID            id of DetectIntent session. required.

```

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and [report issues][google-cloud-php-issues].

## Troubleshooting

If you get the following error, set the environment variable `GCLOUD_PROJECT` to your project ID:

```
[Google\Cloud\Core\Exception\GoogleException]
No project ID was provided, and we were unable to detect a default project ID.
```

If you have not set a timezone you may get an error from php. This can be resolved by:

  1. Finding where the php.ini is stored by running `php -i | grep 'Configuration File'`
  1. Finding out your timezone from the list on this page: http://php.net/manual/en/timezones.php
  1. Editing the php.ini file (or creating one if it doesn't exist)
  1. Adding the timezone to the php.ini file e.g., adding the following line: `date.timezone = "America/Los_Angeles"`

[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
