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

// [START vision_product_search_get_reference_image]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;

/**
 * Get info about a reference image
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 * @param string $referenceImageId ID of the reference image
 */
function product_image_get($projectId, $location, $productId, $referenceImageId)
{
    $client = new ProductSearchClient();

    # get the name of the reference image.
    $referenceImagePath = $client->referenceImageName($projectId, $location, $productId, $referenceImageId);

    # get complete detail of the reference image.
    $referenceImage = $client->getReferenceImage($referenceImagePath);

    # display reference image information
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

    $client->close();
}
// [END vision_product_search_get_reference_image]
