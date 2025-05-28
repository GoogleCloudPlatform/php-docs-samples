<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

declare(strict_types=1);

namespace Google\Cloud\Samples\ModelArmor;

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 3) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId) = $argv;

// [START modelarmor_list_templates]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\ListTemplatesRequest;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // eg. 'my-project'
// $locationId = 'YOUR_LOCATION_ID'; // eg. 'us-central1'

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
$client = new ModelArmorClient($options);

// Build the resource name of the parent location.
$parent = $client->locationName($projectId, $locationId);

// Prepare the request.
$listTemplatesrequest = new ListTemplatesRequest()->setParent($parent);

// Send the request and collect all results.
$templates = iterator_to_array($client->listTemplates($listTemplatesrequest)->iterateAllElements());

foreach ($templates as $template) {
    printf('Template: %s' . PHP_EOL, $template->getName());
}
// [END modelarmor_list_templates]
