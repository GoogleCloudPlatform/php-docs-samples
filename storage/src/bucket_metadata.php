<?php
/**
 * Copyright 2019 Google Inc.
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

# [START storage_get_bucket_metadata]
use Google\Cloud\Storage\StorageClient;

/**
 * Get bucket metadata.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 *
 * @return void
 */
function get_bucket_metadata($bucketName, $objectName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $info = $bucket->info();

    printf("BucketName: %s" . PHP_EOL, $info['name']);
    printf("Location: %s" . PHP_EOL, $info['location']);
    printf("LocationType: %s" . PHP_EOL, $info['locationType']);
    printf("StorageClass: %s" . PHP_EOL, $info['storageClass']);
    printf("TimeCreated: %s" . PHP_EOL, $info['timeCreated']);
    printf("Metageneration: %s" . PHP_EOL, $info['metageneration']);
    printf("DefaultObjectAcl: %s" . PHP_EOL, $info['defaultObjectAcl']);
    if ($info['encrpytion'])
      printf("DefaultKmsKeyName: %s" . PHP_EOL, $info['encryption']['defaultKmsKeyName']);
    if ($info['website'])
      printf("Website: %s" . PHP_EOL, print_r($info['website'], true));
    printf("DefaultEventBasedHold:  %s" . PHP_EOL, $info['defaultEventBasedHold']);
    if ($info['retentionPolicy'])
      printf("RetentionPolicy: %s" . PHP_EOL, print_r($info['retentionPolicy'], true));
    if ($info['billing'])
      printf("RequesterPays: %s" . PHP_EOL, $info['billing']['requesterPays']);
    if ($info['versioning'])
      printf("VersioningEnabled: %s" . PHP_EOL, $info['versioning']['enabled']);
    if ($info['logging'])
      printf("Logging: %s" . PHP_EOL, print_r($info['logging']));
    if ($info['labels'])
      printf("Labels: %s" . PHP_EOL, print_r($info['labels'], true));
}
# [END storage_get_bucket_metadata]
