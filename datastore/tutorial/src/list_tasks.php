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

namespace Google\Cloud\Samples\Datastore\Tasks;

// [START datastore_retrieve_entities]
use Google\Cloud\Datastore\DatastoreClient;

/**
 * Return an iterator for all the tasks in ascending order of creation time.
 *
 * @param string $projectId The Google Cloud project ID.
 */
function list_tasks(string $projectId)
{
    $datastore = new DatastoreClient(['projectId' => $projectId]);

    $query = $datastore->query()
        ->kind('Task')
        ->order('created');
    $result = $datastore->runQuery($query);
    /* @var Entity $task */
    foreach ($result as $index => $task) {
        printf('ID: %s' . PHP_EOL, $task->key()->pathEnd()['id']);
        printf('  Description: %s' . PHP_EOL, $task['description']);
        printf('  Status: %s' . PHP_EOL, $task['done'] ? 'done' : 'created');
        printf('  Created: %s' . PHP_EOL, $task['created']->format('Y-m-d H:i:s e'));
        print(PHP_EOL);
    }
}
// [END datastore_retrieve_entities]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
