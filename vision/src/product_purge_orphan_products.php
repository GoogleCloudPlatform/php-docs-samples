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

# [START vision_product_search_purge_products_in_product_set]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * Delete all products not in any product sets.
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param boolean $force force purge
 */
function purge_orphan_products($projectId, $location, $force)
{
    $client = new ProductSearchClient();

    $parent = $client->locationName($projectId, $location);
    printf("Deleting orphan products" . PHP_EOL);
    $operationResponse  = $client->purgeProducts($parent, ['deleteOrphanProducts' => true, 'force' => $force]);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        print('Operation succeeded' . PHP_EOL);
    # print_r($operationResponse->getResult());
    } else {
        print('Operation failed' . PHP_EOL);
        print_r($operationResponse->getError());
    }
    $client->close();
}
# [END vision_product_search_purge_products_in_product_set]
