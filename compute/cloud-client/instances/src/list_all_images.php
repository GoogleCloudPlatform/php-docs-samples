<?php
/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/compute/cloud-client/README.md
 */

namespace Google\Cloud\Samples\Compute;

# [START compute_images_list]
use Google\Cloud\Compute\V1\ImagesClient;

/**
 * List all images for a particular Cloud project.
 * Example:
 * ```
 * list_all_images($projectId);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_all_images(string $projectId)
{
    $imagesClient = new ImagesClient();
    // Listing only non-deprecated images to reduce the size of the reply.
    $optionalArgs = array('filter' =>"deprecated.state != DEPRECATED");

    // Iterate through all elements using ImagesClient
    $pagedResponse = $imagesClient->list($projectId, $optionalArgs);
    printf("=================== Flat list of images ===================" . PHP_EOL);
    foreach ($pagedResponse->iterateAllElements() as $element) {
        printf(' - %s' . PHP_EOL, $element->getName());
    }
}
# [END compute_images_list]



require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
