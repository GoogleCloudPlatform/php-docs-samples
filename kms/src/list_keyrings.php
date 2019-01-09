<?php
/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/kms/README.md
 */


// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 3) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId) = $argv;

# [START kms_list_keyrings]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The location ID of the crypto key. Can be "global", "us-west1", etc.';

$kms = new KeyManagementServiceClient();

// The resource name of the KeyRing.
$locationName = $kms->locationName($projectId, $locationId);

 // Get the Key Rings and print them.
$keyRings = $kms->listKeyRings($locationName);
foreach ($keyRings as $keyRing) {
    printf("Name: %s\nCreate Time: %s\n",
        $keyRing->getName(),
        date('Y-m-d H:i:s', $keyRing->getCreateTime()->getSeconds())
    );
}
# [END kms_list_keyrings]
