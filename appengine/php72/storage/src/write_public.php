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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/appengine/php72/storage/README.md
 */

namespace Google\Cloud\Samples\AppEngine\Storage;

# [START gae_storage_write_public]
/**
 * Create a file with a public URL.
 * @see https://cloud.google.com/appengine/docs/php/googlestorage/public_access#serving_files_directly_from_google_cloud_storage
 */
function write_public($bucketName, $objectName, $contents)
{
    $options = [
        'gs' => ['predefinedAcl' => 'publicRead']
    ];
    $context = stream_context_create($options);
    $fileName = "gs://${bucketName}/${objectName}";
    file_put_contents($fileName, $contents, 0, $context);

    return sprintf('http://storage.googleapis.com/%s/%s', $bucketName, $objectName);
}
# [END gae_storage_write_public]
