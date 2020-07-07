<?php
/**
 * Copyright 2020 Google LLC.
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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 6 || $argc > 7) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID QUEUE_ID URL SERVICE_ACCOUNT_EMAIL [PAYLOAD]\n", __FILE__);
}
list($_, $projectId, $locationId, $queueId, $url, $serviceAccountEmail) = $argv;
$payload = isset($payload[6]) ? $payload[6] : null;

# [START cloud_tasks_create_http_task_with_token]
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Cloud\Tasks\V2\OidcToken;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The Location ID';
// $queueId = 'The Cloud Tasks Queue ID';
// $url = 'The full url path that the task request will be sent to.';
// $serviceAccountEmail = 'The Cloud IAM service account to be used at the construction of the token';
// $payload = 'The payload your task should carry to the task handler. Optional';

// Instantiate the client and queue name.
$client = new CloudTasksClient();
$queueName = $client->queueName($projectId, $locationId, $queueId);

// Add your service account email to construct the OIDC token
// in order to add an authentication header to the request.
$oidcToken = new OidcToken();
$oidcToken->setServiceAccountEmail($serviceAccountEmail);

// Create an Http Request Object.
$httpRequest = new HttpRequest();
// The full url path that the task request will be sent to.
$httpRequest->setUrl($url);
// POST is the default HTTP method, but any HTTP method can be used.
$httpRequest->setHttpMethod(HttpMethod::POST);
//The oidcToken used to assert identity.
$httpRequest->setOidcToken($oidcToken);
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

# [END cloud_tasks_create_http_task_with_token]
