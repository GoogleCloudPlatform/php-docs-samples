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

// [START vision_product_search_list_products_in_product_set]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * Create a product set
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productSetId ID of the product set
 */
function product_set_list_products($projectId, $location, $productSetId)
{
    $client = new ProductSearchClient();

    # get the name of the product set
    $productSetPath = $client->productSetName($projectId, $location, $productSetId);

    # list all the products available in the product set.
    $products = $client->listProductsInProductSet($productSetPath);

    # display the product information.
    foreach ($products->iterateAllElements() as $product) {
        $name = $product->getName();
        $nameArray = explode('/', $name);

        printf('Product name: %s' . PHP_EOL, $name);
        printf('Product id: %s' . PHP_EOL, end($nameArray));
        printf('Product display name: %s' . PHP_EOL, $product->getDisplayName());
        printf('Product description: %s' . PHP_EOL, $product->getDescription());
        printf('Product category: %s' . PHP_EOL, $product->getProductCategory());
        print('Product labels: ' . PHP_EOL);
        foreach ($product->getProductLabels() as $label) {
            printf('%s : %s' . PHP_EOL, $label->getKey(), $label->getValue());
        }
        print(PHP_EOL);
    }
    $client->close();
}
// [END vision_product_search_list_products_in_product_set]
