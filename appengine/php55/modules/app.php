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
namespace Google\Cloud\Samples\Appengine\Modules;

use Silex\Application;
// [START import]
use google\appengine\api\modules\ModulesService;

// [END import]

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    // [START simple_methods]
    $module = ModulesService::getCurrentModuleName();
    $instance = ModulesService::getCurrentInstanceId();
    // [END simple_methods]
    return "$module:$instance";
});

$app->get('/access_backend', function () use ($app) {
    // [START access_another_module]
    $url = 'http://' . ModulesService::getHostname('my-backend') . '/';
    $result = file_get_contents($url);
    // [END access_another_module]
    return $result;
});

return $app;
