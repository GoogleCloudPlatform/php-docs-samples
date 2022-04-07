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

# [START compute_images_list_page]
use Google\Cloud\Compute\V1\ImagesClient;

/**
 * Prints a list of all non-deprecated image names available in a given project,
 * divided into pages as returned by the Compute Engine API.
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to list images from.
 * @param int $pageSize Size of the pages you want the API to return on each call.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_images_by_page(string $projectId, int $pageSize = 10)
{
    $imagesClient = new ImagesClient();
    $pageNum = 1;
    // Listing only non-deprecated images to reduce the size of the reply.
    $optionalArgs = ['maxResults' => $pageSize, 'filter' => 'deprecated.state != DEPRECATED'];

    /**
     * Use the 'iteratePages()' method of returned response to have more granular control of iteration over
     * paginated results from the API. Each time you want to access the next page, the library retrieves
     * that page from the API.
     */
    $pagedResponse = $imagesClient->list($projectId, $optionalArgs);
    print('=================== Paginated list of images ===================' . PHP_EOL);
    foreach ($pagedResponse->iteratePages() as $page) {
        printf('Page %s:' . PHP_EOL, $pageNum);
        foreach ($page as $element) {
            printf(' - %s' . PHP_EOL, $element->getName());
        }
        $pageNum++;
    }
}
# [END compute_images_list_page]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
