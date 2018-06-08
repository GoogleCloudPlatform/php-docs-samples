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

// [START use_task_and_queue]
// [START use_task]
use google\appengine\api\taskqueue\PushTask;
// [END use_task]
use google\appengine\api\taskqueue\PushQueue;
// [END use_task_and_queue]
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    // [START add_task]
    $task = new PushTask(
        '/worker',
        ['name' => 'john doe', 'action' => 'send_reminder']);
    $task_name = $task->add();
    // [END add_task]
    // [START add_tasks]
    $task1 = new PushTask('/someUrl');
    $task2 = new PushTask('/someOtherUrl');
    $queue = new PushQueue();
    $queue->addTasks([$task1, $task2]);
    // [END add_tasks]
    // [START url_endpoints]
    (new PushTask('/path/to/my/worker', ['data_for_task' => 1234]))->add();
    // [END url_endpoints]
    return 'A task ' . $task_name . ' added.';
});

$app->post('/worker', function (Request $req) use ($app) {
    return 'name: ' . $req->get('name') . "\n"
        . 'action: ' . $req->get('action');
});

$app->post('/someUrl', function (Request $req) use ($app) {
    return 'Ok';
});

$app->post('/someOtherUrl', function (Request $req) use ($app) {
    return 'Ok';
});

$app->post('/path/to/my/worker', function (Request $req) use ($app) {
    return 'Ok';
});

return $app;
