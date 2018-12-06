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

// [START vision_product_search_get_similar_products_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\ProductSearchParams;

/**
 * Search similar products to image
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productSetId ID of the product set
 * @param string $productCategory Category of the product
 * @param string $gcs Google Cloud Storage path of the image to be searched
 * @param string $filter Condition to be applied on the labels
 */
function product_search_similar_gcs($projectId, $location, $productSetId, $productCategory, $gcsUri, $filter)
{
    $imageAnnotatorClient = new ImageAnnotatorClient();
    $productSearchClient = new ProductSearchClient();

    # get the name of the product set
    $productSetPath = $productSearchClient->productSetName($projectId, $location, $productSetId);

    # product search specific parameters
    $productSearchParams = (new ProductSearchParams())
        ->setProductSet($productSetPath)
        ->setProductCategories([$productCategory])
        ->setFilter($filter);

    # search products similar to the image
    $response = $imageAnnotatorClient->productSearch($gcsUri, $productSearchParams);

    if ($productSearchResults = $response->getProductSearchResults()) {
        $indexTime = $productSearchResults->getIndexTime();
        printf('Product set index time: %d seconds %d nanos' . PHP_EOL, $indexTime->getSeconds(), $indexTime->getNanos());
        
        $results = $productSearchResults->getResults();
        print('Search results: ' . PHP_EOL);
        foreach ($results as $result) {
            printf('Score (confidence): %d' . PHP_EOL, $result->getScore());

            # display the product information.
            $product = $result->getProduct();
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
        }
    } else {
        print($response->getError()->getMessage());
    }

    $imageAnnotatorClient->close();
    $productSearchClient->close();
}
// [END vision_product_search_get_similar_products_gcs]
