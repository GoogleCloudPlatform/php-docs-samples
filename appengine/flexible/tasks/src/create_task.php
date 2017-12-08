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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/appengine/flexible/tasks/README.md
 */

# [START create_task]
namespace Google\Cloud\Samples\Tasks;

use Google_Client;
use Google_Service_CloudTasks;
use Google_Service_CloudTasks_AppEngineHttpRequest;
use Google_Service_CloudTasks_Task;
use Google_Service_CloudTasks_CreateTaskRequest;

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
 * @param integer $in_seconds The number of seconds from now to schedule task attempt.
 *
 */
function create_task($projectId, $queueId, $location, $payload = 'helloworld', $in_seconds = null)
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud Tasks client.
    $tasks_client = new Google_Service_CloudTasks($client);

    // Create an App Engine HTTP Request object.
    $appEngineHttpRequest = new Google_Service_CloudTasks_AppEngineHttpRequest();
    $appEngineHttpRequest->setHttpMethod('POST');
    $appEngineHttpRequest->setPayload(base64_encode($payload));
    $appEngineHttpRequest->setRelativeUrl('/log_payload');

    // Create a Cloud Task object.
    $task = new Google_Service_CloudTasks_Task();
    $task->setAppEngineHttpRequest($appEngineHttpRequest);

    // If in_seconds variable is set, set the future time for when the task will be attempted.
    if ($in_seconds != null) {
        $seconds_string = sprintf('+%s seconds', $in_seconds);
        $future_time = date(\DateTime::RFC3339, strtotime($seconds_string));
        printf('Future time is: %s' . PHP_EOL, $future_time);
        $task->setScheduleTime($future_time);
    }

    // Create a Create Task Request object.
    $createTaskRequest = new Google_Service_CloudTasks_CreateTaskRequest();
    $createTaskRequest->setTask($task);

    // Create queue name using queue ID passed in by user.
    $queue_name = sprintf('projects/%s/locations/%s/queues/%s',
        $projectId,
        $location,
        $queueId
    );

    // Send request and print the task name.
    $response = $tasks_client->projects_locations_queues_tasks->create(
        $queue_name,
        $createTaskRequest
    );
    printf('Created task %s' . PHP_EOL, $response['name']);
}
# [END create_task]
