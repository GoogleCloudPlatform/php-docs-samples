<?php
/**
 * Copyright 2018 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/appengine/php72/tasks/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 4 || $argc > 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID QUEUE_ID [PAYLOAD]\n", __FILE__);
}
list($_, $projectId, $locationId, $queueId, $payload) = $argv;

# [START cloud_tasks_appengine_create_task]
use Google\Cloud\Tasks\V2\AppEngineHttpRequest;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\Task;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The Location ID';
// $queueId = 'The Cloud Tasks App Engine Queue ID';
// $payload = 'The payload your task should carry to the task handler. Optional';

// Instantiate the client and queue name.
$client = new CloudTasksClient();
$queueName = $client->queueName($projectId, $locationId, $queueId);

// Create an App Engine Http Request Object.
$httpRequest = new AppEngineHttpRequest();
// The path of the HTTP request to the App Engine service.
$httpRequest->setRelativeUri('/task_handler');
// POST is the default HTTP method, but any HTTP method can be used.
$httpRequest->setHttpMethod(HttpMethod::POST);
// Setting a body value is only compatible with HTTP POST and PUT requests.
if (isset($payload)) {
    $httpRequest->setBody($payload);
}

// Create a Cloud Task object.
$task = new Task();
$task->setAppEngineHttpRequest($httpRequest);

// Send request and print the task name.
$response = $client->createTask($queueName, $task);
printf('Created task %s' . PHP_EOL, $response->getName());

# [END cloud_tasks_appengine_create_task]
