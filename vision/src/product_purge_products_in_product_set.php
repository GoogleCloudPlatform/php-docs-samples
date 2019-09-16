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
use Google\Cloud\Vision\V1\ProductSetPurgeConfig;

/**
 * Delete all products in a product set.
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $product_set_id ID of the product
 * @param boolean $force force purge
 */
function purge_products_in_product_set($projectId, $location, $product_set_id, $force)
{
    $client = new ProductSearchClient();

    $parent = $client->locationName($projectId, $location);
    $product_set_purge_config = (new ProductSetPurgeConfig())->setProductSetId($product_set_id);
    printf("Deleting products in product-set: %s" . PHP_EOL, $product_set_id);
    $operationResponse  = $client->purgeProducts($parent, ['productSetPurgeConfig' => $product_set_purge_config,
        'force' => $force]);
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
