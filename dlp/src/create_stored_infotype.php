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

# [START dlp_create_stored_infotype]

use Google\Cloud\Dlp\V2\BigQueryField;
use Google\Cloud\Dlp\V2\BigQueryTable;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\CloudStoragePath;
use Google\Cloud\Dlp\V2\CreateStoredInfoTypeRequest;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\LargeCustomDictionaryConfig;
use Google\Cloud\Dlp\V2\StoredInfoTypeConfig;

/**
 * Create a stored infoType.
 *
 * @param string $callingProjectId  The Google Cloud Project ID to run the API call under.
 * @param string $outputgcsPath     The path to the location in a Cloud Storage bucket to store the created dictionary.
 * @param string $storedInfoTypeId  The name of the custom stored info type.
 * @param string $displayName       The human-readable name to give the stored infoType.
 * @param string $description       A description for the stored infoType to be created.
 */
function create_stored_infotype(
    string $callingProjectId,
    string $outputgcsPath,
    string $storedInfoTypeId,
    string $displayName,
    string $description
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The reference to the table containing the GitHub usernames.
    // The reference to the BigQuery field that contains the GitHub usernames.
    // Note: we have used public data
    $bigQueryField = (new BigQueryField())
        ->setTable((new BigQueryTable())
            ->setDatasetId('samples')
            ->setProjectId('bigquery-public-data')
            ->setTableId('github_nested'))
        ->setField((new FieldId())
            ->setName('actor'));

    $largeCustomDictionaryConfig = (new LargeCustomDictionaryConfig())
        // The output path where the custom dictionary containing the GitHub usernames will be stored.
        ->setOutputPath((new CloudStoragePath())
            ->setPath($outputgcsPath))
        ->setBigQueryField($bigQueryField);

    // Configure the StoredInfoType we want the service to perform.
    $storedInfoTypeConfig = (new StoredInfoTypeConfig())
        ->setDisplayName($displayName)
        ->setDescription($description)
        ->setLargeCustomDictionary($largeCustomDictionaryConfig);

    // Send the stored infoType creation request and process the response.
    $parent = "projects/$callingProjectId/locations/global";
    $createStoredInfoTypeRequest = (new CreateStoredInfoTypeRequest())
        ->setParent($parent)
        ->setConfig($storedInfoTypeConfig)
        ->setStoredInfoTypeId($storedInfoTypeId);
    $response = $dlp->createStoredInfoType($createStoredInfoTypeRequest);

    // Print results.
    printf('Successfully created Stored InfoType : %s', $response->getName());
}
# [END dlp_create_stored_infotype]
// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
