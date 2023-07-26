<?php
/**
 * Copyright 2023 Google Inc.
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
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

// [START dlp_delete_stored_infotype]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * Delete results of a Data Loss Prevention API stored infotype.
 *
 * @param string $storedInfoTypeName The name of the stored infotype whose results should be deleted.
 */
function delete_stored_infotype(string $storedInfoTypeName): void
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Run stored infotype deletion request.
    // The Parent project ID is automatically extracted from this parameter.
    $dlp->deleteStoredInfoType($storedInfoTypeName);

    // Print status
    printf('Successfully deleted stored infotype %s' . PHP_EOL, $storedInfoTypeName);
}
// [END dlp_delete_stored_infotype]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
