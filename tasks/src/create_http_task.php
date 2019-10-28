<?php
/**
 * Copyright 2019 Google LLC.
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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 5 || $argc > 6) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID QUEUE_ID URL [PAYLOAD]\n", __FILE__);
}
list($_, $projectId, $locationId, $queueId, $url, $payload) = $argv;

# [START cloud_tasks_create_http_task]
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The Location ID';
// $queueId = 'The Cloud Tasks Queue ID';
// $url = 'The full url path that the task request will be sent to.'
// $payload = 'The payload your task should carry to the task handler. Optional';

// Instantiate the client and queue name.
$client = new CloudTasksClient();
$queueName = $client->queueName($projectId, $locationId, $queueId);

// Create an Http Request Object.
$httpRequest = new HttpRequest();
// The full url path that the task request will be sent to.
$httpRequest->setUrl($url);
// POST is the default HTTP method, but any HTTP method can be used.
$httpRequest->setHttpMethod(HttpMethod::POST);
// Setting a body value is only compatible with HTTP POST and PUT requests.
if (isset($payload)) {
    $httpRequest->setBody($payload);
}

// Create a Cloud Task object.
$task = new Task();
$task->setHttpRequest($httpRequest);

// Send request and print the task name.
$response = $client->createTask($queueName, $task);
printf('Created task %s' . PHP_EOL, $response->getName());

# [END cloud_tasks_create_http_task]
