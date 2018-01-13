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
use Google_Service_CloudTasks_PullMessage;
use Google_Service_CloudTasks_Task;
use Google_Service_CloudTasks_CreateTaskRequest;

# [START create_task]
/**
 * Create a task for a given App Engine queue
 * ```
 * create_task(PROJECT_ID, QUEUE_ID, LOCATION, PAYLOAD, SECONDS)
 * ```
 *
 * @param string $projectId Project of the queue to add the task to.
 * @param string $queueID ID (short name) of the queue to add the task to.
 * @param string $location Location of the queue to add the task to.
 * @param string $payload Optional payload to attach to the App Engine HTTP request.
 *
 */
function create_task($projectId, $queueId, $location, $payload = 'helloworld')
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud Tasks client.
    $tasksClient = new Google_Service_CloudTasks($client);

    // Create a Pull Message Object.
    $pullMessage = new Google_Service_CloudTasks_PullMessage();
    $pullMessage->setPayload(base64_encode($payload));

    // Create a Cloud Task object.
    $task = new Google_Service_CloudTasks_Task();
    $task->setPullMessage($pullMessage);

    // Create a Create Task Request object.
    $createTaskRequest = new Google_Service_CloudTasks_CreateTaskRequest();
    $createTaskRequest->setTask($task);

    // Create queue name using queue ID passed in by user.
    $queueName = sprintf('projects/%s/locations/%s/queues/%s',
        $projectId,
        $location,
        $queueId
    );

    // Send request and print the task name.
    $response = $tasksClient->projects_locations_queues_tasks->create(
        $queueName,
        $createTaskRequest
    );
    printf('Created task %s' . PHP_EOL, $response['name']);
}
# [END create_task]
