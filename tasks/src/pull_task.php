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
use Google_Service_CloudTasks_LeaseTasksRequest;

# [START pull_task]
/**
 * Pull a task from a given Pull Queue
 * ```
 * pull_task(PROJECT_ID, QUEUE_ID, LOCATION)
 * ```
 *
 * @param string $projectId Project of the queue to pull the task from.
 * @param string $queueID ID (short name) of the queue to pull the task from.
 * @param string $location Location of the queue to pull the task from.
 *
 */
function pull_task($projectId, $queueId, $location)
{
    // Instantiate the client, authenticate, and add scopes.
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    // Create the Cloud Tasks client.
    $tasksClient = new Google_Service_CloudTasks($client);

    // Create a Lease Tasks Request object.
    $leaseTasksRequest = new Google_Service_CloudTasks_LeaseTasksRequest();
    $leaseTasksRequest->setMaxTasks(1);
    $leaseTasksRequest->setLeaseDuration('60s');
    $leaseTasksRequest->setResponseView('FULL');

    // Create queue name using queue ID passed in by user.
    $queueName = sprintf('projects/%s/locations/%s/queues/%s',
        $projectId,
        $location,
        $queueId
    );

    // Send request and return the task to the caller.
    $response = $tasksClient->projects_locations_queues_tasks->lease(
        $queueName,
        $leaseTasksRequest
    );
    printf('Pulled task %s' . PHP_EOL, $response->getTasks()[0]->getName());
    return $response->getTasks()[0];
}
# [END pull_task]
