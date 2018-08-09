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


// composer autoloading
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/app.php';

$app['project_id'] = getenv('GOOGLE_PROJECT_ID') ?: getenv('GCLOUD_PROJECT');
# [START gae_flex_pubsub_env]
$app['topic'] = 'php-example-topic';
$app['subscription'] = 'php-example-subscription';
# [END gae_flex_pubsub_env]
$app['debug'] = true;

$app->run();
