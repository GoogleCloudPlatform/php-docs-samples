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

# [START datastore_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Datastore\DatastoreClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$datastore = new DatastoreClient([
    'projectId' => $projectId
]);

# The kind for the new entity
$kind = 'Task';

# The name/ID for the new entity
$name = 'sampletask1';

# The Cloud Datastore key for the new entity
$taskKey = $datastore->key($kind, $name);

# Prepares the new entity
$task = $datastore->entity($taskKey, ['description' => 'Buy milk']);

# Saves the entity
$datastore->upsert($task);

echo 'Saved ' . $task->key() . ': ' . $task['description'] . PHP_EOL;
# [END datastore_quickstart]
return $task;
