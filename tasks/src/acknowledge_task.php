<?php
/**
 * Copyright 2017 Google Inc.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/tasks/README.md
 */

namespace Google\Cloud\Samples\Tasks;

use Google_Client;
use Google_Service_CloudTasks;
use Google_Service_CloudTasks_Task;
use Google_Service_CloudTasks_AcknowledgeTaskRequest;

# [START acknowledge_task]
/**
 * Acknowledge a task from a given Pull Queue
 * ```
 * acknowledge_task(TASK)
 * ```
 *
 * @param Google_Service_CloudTasks_Task $task A task that was pulled from a Pull Queue.
 *
 */
function acknowledge_task(Google_Service_CloudTasks_Task $task)
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud Tasks client.
    $tasksClient = new Google_Service_CloudTasks($client);

    // Create an Acknowledge Task Request.
    $acknowledgeTaskRequest = new Google_Service_CloudTasks_AcknowledgeTaskRequest();
    $acknowledgeTaskRequest->setScheduleTime($task->getScheduleTime());

    // Execute Acknowledge Task Request.
    $tasksClient->projects_locations_queues_tasks->acknowledge(
        $task->getName(),
        $acknowledgeTaskRequest
    );
    printf('Acknowledged task %s' . PHP_EOL, $task->getName());
}
# [END acknowledge_task]
