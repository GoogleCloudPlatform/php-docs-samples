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

// [START vision_product_search_update_product_labels]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\Product\KeyValue;
use Google\Cloud\Vision\V1\Product;
use Google\Protobuf\FieldMask;

/**
 * Update product labels
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 * @param string $key Key of the label to update
 * @param string $value Value of label to update
 */
function product_update($projectId, $location, $productId, $key, $value)
{
    $client = new ProductSearchClient();

    # get the name of the product.
    $productPath = $client->productName($projectId, $location, $productId);

    # set product name, product label and product display name.
    # multiple labels are also supported.
    $keyValue = (new KeyValue())
        ->setKey($key)
        ->setValue($value);
    $product = (new Product())
        ->setName($productPath)
        ->setProductLabels([$keyValue]);

    # updating only the product labels field here.
    $updateMask = (new FieldMask())
        ->setPaths(['product_labels']);

    # this overwrites the product_labels.
    $updatedProduct = $client->updateProduct($product, ['updateMask' => $updateMask]);

    # display the product information.
    printf('Product name: %s' . PHP_EOL, $updatedProduct->getName());
    print('Product labels: ' . PHP_EOL);
    foreach ($product->getProductLabels() as $label) {
        printf('%s : %s' . PHP_EOL, $label->getKey(), $label->getValue());
    }
    
    $client->close();
}
// [END vision_product_search_update_product_labels]
