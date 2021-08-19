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
 * List all images for a particular Cloud project in pages.
 * Example:
 * ```
 * list_images_by_page($projectId);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param int $page_size Size of the pages you want the API to return on each call.
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_images_by_page(string $projectId, int $page_size = 10)
{
    $imagesClient = new ImagesClient();
    $page_num = 1;
    $optionalArgs = array( 'maxResults' => $page_size, 'filter' =>"deprecated.state != DEPRECATED");
    // Iterate through elements by page using ImagesClient
    $pagedResponse = $imagesClient->list($projectId, $optionalArgs);
    printf("=================== Paginated list of images ===================" . PHP_EOL);
    foreach ($pagedResponse->iteratePages() as $page) {
        printf('Page ' . $page_num . ':' . PHP_EOL);
        foreach ($page as $element) {
            printf(' - %s' . PHP_EOL, $element->getName());
            $page_num++;
        }
    }
}
# [END compute_images_list_page]



require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
