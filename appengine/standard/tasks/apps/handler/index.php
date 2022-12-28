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

// [START cloud_tasks_appengine_quickstart]

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Logging\LoggingClient;

// Create the logging client.
$logging = new LoggingClient();
// Create a PSR-3-compatible logger.
$logger = $logging->psrLogger('app', ['batchEnabled' => true]);

// Front-controller to route requests.
switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        print "Hello, World!\n";
        break;
    case '/task_handler':
        // Taskname and Queuename are two of several useful Cloud Tasks headers available on the request.
        $taskName = $_SERVER['HTTP_X_APPENGINE_TASKNAME'] ?? '';
        $queueName = $_SERVER['HTTP_X_APPENGINE_QUEUENAME'] ?? '';

        try {
            handle_task(
                $queueName,
                $taskName,
                file_get_contents('php://input')
            );
        } catch (Exception $e) {
            http_response_code(400);
            exit($e->getMessage());
        }
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}

/**
 * Process a Cloud Tasks HTTP Request.
 *
 * @param string $queueName provides the name of the queue which dispatched the task.
 * @param string $taskName provides the identifier of the task.
 * @param string $body The task details from the HTTP request.
 */
function handle_task($queueName, $taskName, $body = '')
{
    global $logger;

    if (empty($taskName)) {
        // You may use the presence of the X-Appengine-Taskname header to validate
        // the request comes from Cloud Tasks.
        $logger->warning('Invalid Task: No X-Appengine-Taskname request header found');
        throw new Exception('Bad Request - Invalid Task');
    }

    $output = sprintf('Completed task: task queue(%s), task name(%s), payload(%s)', $queueName, $taskName, $body);
    $logger->info($output);

    // Set a non-2xx status code to indicate a failure in task processing that should be retried.
    // For example, http_response_code(500) to indicate a server error.
    print $output;
}

// [END cloud_tasks_appengine_quickstart]
