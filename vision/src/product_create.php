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

// [START vision_product_search_create_product]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\Product;

/**
 * Create one product
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 * @param string $productDisplayName Display name of the product
 * @param string $productCategory Category of the product
 */
function product_create($projectId, $location, $productId, $productDisplayName, $productCategory)
{
    $client = new ProductSearchClient();

    # a resource that represents Google Cloud Platform location.
    $locationPath = $client->locationName($projectId, $location);

    # create a product with the product specification in the region.
    # set product name and product display name.
    $product = (new Product())
        ->setDisplayName($productDisplayName)
        ->setProductCategory($productCategory);

    # the response is the product with the `name` field populated.
    $response = $client->createProduct($locationPath, $product, ['productId' => $productId]);

    # display the product information.
    printf('Product name: %s' . PHP_EOL, $response->getName());
    
    $client->close();
}
// [END vision_product_search_create_product]
