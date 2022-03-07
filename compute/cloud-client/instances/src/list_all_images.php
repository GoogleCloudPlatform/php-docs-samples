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
 * Prints a list of all non-deprecated image names available in given project.
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to list images from.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_all_images(string $projectId)
{
    $imagesClient = new ImagesClient();
    // Listing only non-deprecated images to reduce the size of the reply.
    $optionalArgs = ['maxResults' => 100, 'filter' => 'deprecated.state != DEPRECATED'];

    /**
     * Although the maxResults parameter is specified in the request, the iterateAllElements() method
     * hides the pagination mechanic. The library makes multiple requests to the API for you,
     * so you can simply iterate over all the images.
     */
    $pagedResponse = $imagesClient->list($projectId, $optionalArgs);
    print('=================== Flat list of images ===================' . PHP_EOL);
    foreach ($pagedResponse->iterateAllElements() as $element) {
        printf(' - %s' . PHP_EOL, $element->getName());
    }
}
# [END compute_images_list]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
