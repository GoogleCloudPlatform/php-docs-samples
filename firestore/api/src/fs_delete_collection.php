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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

/**
 * Delete a collection.
 * ```
 * fs_delete_collection();
 * ```
 *
 */

# [START fs_delete_collection]
function fs_delete_collection($collectionReference, $batchSize)
{
    $documents = $collectionReference->limit($batchSize)->documents();
    $numberDeleted = 0;

    foreach ($documents as $document) {
    	printf('Deleting document %s' . PHP_EOL, $document->id());
    	$document->reference()->delete();
    	$numberDeleted++;
    }

    if ($numberDeleted >= $batchSize) {
    	fs_delete_collection($collectionReference, $batchSize);
    }
}
# [END fs_delete_collection]
