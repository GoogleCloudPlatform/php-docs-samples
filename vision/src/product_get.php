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

// [START vision_product_search_get_product]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * Get information about a product
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 */
function product_get($projectId, $location, $productId)
{
    $client = new ProductSearchClient();

    # get the name of the product.
    $productPath = $client->productName($projectId, $location, $productId);

    # get complete detail of the product.
    $product = $client->getProduct($productPath);

    # display the product information.
    $productName = $product->getName();
    $productNameArray = explode('/', $productName);

    printf('Product name: %s' . PHP_EOL, $productName);
    printf('Product id: %s' . PHP_EOL, end($productNameArray));
    printf('Product display name: %s' . PHP_EOL, $product->getDisplayName());
    printf('Product description: %s' . PHP_EOL, $product->getDescription());
    printf('Product category: %s' . PHP_EOL, $product->getProductCategory());
    print('Product labels: ' . PHP_EOL);
    foreach ($product->getProductLabels() as $label) {
        printf('%s : %s' . PHP_EOL, $label->getKey(), $label->getValue());
    }
    $client->close();
}
// [END vision_product_search_get_product]
