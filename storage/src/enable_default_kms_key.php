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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_set_bucket_default_kms_key]
use Google\Cloud\Storage\StorageClient;

/**
 * Enable a bucket's requesterpays metadata.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $bucketName Name of your Google Cloud Storage bucket.
 * @param string $kmsKeyName KMS key ID to use as the default KMS key.
 *
 * @return void
 */
function enable_default_kms_key($projectId, $bucketName, $kmsKeyName)
{
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);
    $bucket = $storage->bucket($bucketName);
    $bucket->update([
        'encryption' => [
            'defaultKmsKeyName' => $kmsKeyName
        ]
    ]);
    printf('Default KMS key for %s was set to %s' . PHP_EOL,
        $bucketName,
        $bucket->info()['encryption']['defaultKmsKeyName']);
}
# [END storage_set_bucket_default_kms_key]
