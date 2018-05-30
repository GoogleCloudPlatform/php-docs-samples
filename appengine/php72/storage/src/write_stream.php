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

# [START gae_storage_write_stream]
/**
 * Write to a Storage bucket using a stream.
 * @see https://cloud.google.com/appengine/docs/php/googlestorage/#streamed_file_write
 */
function write_stream($bucketName, $objectName, $contents)
{
    $fp = fopen("gs://${bucketName}/${objectName}", 'w');
    fwrite($fp, $contents);
    fclose($fp);
}
# [END gae_storage_write_stream]
