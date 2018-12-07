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

// [START vision_product_search_create_product_set]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\ProductSet;

/**
 * Create a product set
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productSetId ID of the product set
 * @param string $productSetDisplayName Display name of the product set
 */
function product_set_create($projectId, $location, $productSetId, $productSetDisplayName)
{
    $client = new ProductSearchClient();

    # a resource that represents Google Cloud Platform location.
    $locationPath = $client->locationName($projectId, $location);

    # create a product set with the product set specification in the region.
    $productSet = (new ProductSet())
        ->setDisplayName($productSetDisplayName);

    # the response is the product set with the `name` field populated.
    $response = $client->createProductSet($locationPath, $productSet, ['productSetId' => $productSetId]);

    # display the product information.
    printf('Product set name: %s' . PHP_EOL, $response->getName());
    
    $client->close();
}
// [END vision_product_search_create_product_set]
