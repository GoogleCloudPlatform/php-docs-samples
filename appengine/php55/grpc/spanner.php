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

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Spanner\SpannerClient;

# Imports the App Engine SDK
use google\appengine\api\app_identity\AppIdentityService;

# Your Google Cloud Platform project ID
$projectId = AppIdentityService::getApplicationId();

# Instantiates a client
$spanner = new SpannerClient([
    'projectId' => $projectId
]);

# Your Cloud Spanner instance ID.
$instanceId = 'your-instance-id';

# Get a Cloud Spanner instance by ID.
$instance = $spanner->instance($instanceId);

# Your Cloud Spanner database ID.
$databaseId = 'your-database-id';

# Get a Cloud Spanner database by ID.
$database = $instance->database($databaseId);

# Execute a simple SQL statement.
$results = $database->execute('SELECT "Hello World" as test');

foreach ($results as $row) {
    print($row['test'] . PHP_EOL);
}
// [END spanner_quickstart]
return $results;
