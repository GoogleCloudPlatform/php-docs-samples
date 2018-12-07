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

// [START vision_product_search_list_product_sets]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * List all product set
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 */
function product_set_list($projectId, $location)
{
    $client = new ProductSearchClient();

    # a resource that represents Google Cloud Platform location.
    $locationPath = $client->locationName($projectId, $location);

    # a resource that represents Google Cloud Platform location.
    $productSets = $client->listProductSets($locationPath);

    # display the product set information.
    foreach ($productSets->iterateAllElements() as $productSet) {
        $name = $productSet->getName();
        $nameArray = explode('/', $name);
        $indexTime = $productSet->getIndexTime();

        printf('Product set name: %s' . PHP_EOL, $name);
        printf('Product set id: %s' . PHP_EOL, end($nameArray));
        printf('Product set display name: %s' . PHP_EOL, $productSet->getDisplayName());
        printf('Product set index time: %d seconds %d nanos' . PHP_EOL, $indexTime->getSeconds(), $indexTime->getNanos());
        print(PHP_EOL);
    }
    
    $client->close();
}
// [END vision_product_search_list_product_sets]
