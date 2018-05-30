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

# [START upload_file]
/**
 * Handle an uploaded file.
 * @see https://cloud.google.com/appengine/docs/php/googlestorage/user_upload#implementing_file_uploads
 */
function upload_file($bucketName)
{
    $fileName = $_FILES['uploaded_files']['name'];
    $tempName = $_FILES['uploaded_files']['tmp_name'];
    move_uploaded_file($tempName, "gs://${bucketName}/${fileName}.txt");
    sprintf('Your file "%s" has been uploaded.', $fileName);
}
# [END upload_file]
