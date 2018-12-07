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

// [START vision_product_search_list_reference_images]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * List all images in
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 */
function product_image_list($projectId, $location, $productId)
{
    $client = new ProductSearchClient();

    # get the name of the product.
    $productPath = $client->productName($projectId, $location, $productId);

    # list all the reference images available
    $referenceImages = $client->listReferenceImages($productPath);

    foreach ($referenceImages->iterateAllElements() as $referenceImage) {
        $name = $referenceImage->getName();
        $nameArray = explode('/', $name);

        printf('Reference image name: %s' . PHP_EOL, $name);
        printf('Reference image id: %s' . PHP_EOL, end($nameArray));
        printf('Reference image uri: %s' . PHP_EOL, $referenceImage->getUri());
        print('Reference image bounding polygons: ');
        foreach ($referenceImage->getBoundingPolys() as $boundingPoly) {
            foreach ($boundingPoly->getVertices() as $vertex) {
                printf('(%d, %d) ', $vertex->getX(), $vertex->getY());
            }
            print(PHP_EOL);
        }
    }

    $client->close();
}
// [END vision_product_search_list_reference_images]
