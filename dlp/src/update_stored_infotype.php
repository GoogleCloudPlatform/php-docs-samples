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

# [START dlp_update_stored_infotype]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\CloudStorageFileSet;
use Google\Cloud\Dlp\V2\CloudStoragePath;
use Google\Cloud\Dlp\V2\LargeCustomDictionaryConfig;
use Google\Cloud\Dlp\V2\StoredInfoTypeConfig;
use Google\Protobuf\FieldMask;

/**
 * Rebuild/Update the stored infoType.
 *
 * @param string $callingProjectId  The project ID to run the API call under.
 * @param string $gcsPath           The path to file in GCS bucket that holds a collection of words and phrases to be searched by the new infoType detector.
 * @param string $outputgcsPath     The path to the location in a Cloud Storage bucket to store the created dictionary.
 * @param string $storedInfoTypeId  The name of the stored InfoType which is to be updated.
 *
 */
function update_stored_infotype(
    string $callingProjectId,
    string $gcsPath,
    string $outputgcsPath,
    string $storedInfoTypeId
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Set path in Cloud Storage.
    $cloudStorageFileSet = (new CloudStorageFileSet())
        ->setUrl($gcsPath);

    // Configuration for a custom dictionary created from a data source of any size
    $largeCustomDictionaryConfig = (new LargeCustomDictionaryConfig())
        ->setOutputPath((new CloudStoragePath())
            ->setPath($outputgcsPath))
        ->setCloudStorageFileSet($cloudStorageFileSet);

    // Set configuration for stored infoTypes.
    $storedInfoTypeConfig = (new StoredInfoTypeConfig())
        ->setLargeCustomDictionary($largeCustomDictionaryConfig);

    // Send the stored infoType creation request and process the response.

    // $name = "projects/$callingProjectId/locations/global/storedInfoTypes/" . $storedInfoTypeId;
    $name = $dlp->projectLocationStoredInfoTypeName($callingProjectId, 'global', $storedInfoTypeId);
    // Set mask to control which fields get updated.
    // Refer https://protobuf.dev/reference/protobuf/google.protobuf/#field-mask for constructing the field mask paths.
    $fieldMask = (new FieldMask())
        ->setPaths([
            'large_custom_dictionary.cloud_storage_file_set.url'
        ]);

    // Run request
    $response = $dlp->updateStoredInfoType($name, [
        'config' => $storedInfoTypeConfig,
        'updateMask' => $fieldMask
    ]);

    // Print results
    printf('Successfully update Stored InforType : %s' . PHP_EOL, $response->getName());
}
# [END dlp_update_stored_infotype]
// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
