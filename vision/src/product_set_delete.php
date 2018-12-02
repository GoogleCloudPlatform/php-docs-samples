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

// [START vision_product_search_delete_product_set]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * Deletes product set
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productSetId ID of the product
 */
function product_set_delete($projectId, $location, $productSetId)
{
    $client = new ProductSearchClient();

    # get the name of the product set
    $productSetPath = $client->productSetName($projectId, $location, $productSetId);

    # delete the product set
    $client->deleteProductSet($productSetPath);
    print('Product set deleted.' . PHP_EOL);

    $client->close();
}
// [END vision_product_search_delete_product_set]
