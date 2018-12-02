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

// [START vision_product_search_create_reference_image]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\ReferenceImage;

/**
 * Create a reference image
 *
 * @param string $projectId Your Google Cloud project ID
 * @param string $location Google Cloud compute region name
 * @param string $productId ID of the product
 * @param string $referenceImageId ID of the reference image
 * @param string $gcsUri Google Cloud Storage path of the input image
 */
function product_image_create($projectId, $location, $productId, $referenceImageId, $gcsUri)
{
    $client = new ProductSearchClient();

    # get the name of the product.
    $productPath = $client->productName($projectId, $location, $productId);

    # create a reference image.
    $referenceImage = (new ReferenceImage())
        ->setUri($gcsUri);

    # the response is the reference image with `name` populated.
    $image = $client->createReferenceImage($productPath, $referenceImage, ['referenceImageId' => $referenceImageId]);

    # display the reference image information
    printf('Reference image name: %s' . PHP_EOL, $image->getName());
    printf('Reference image uri: %s' . PHP_EOL, $image->getUri());

    $client->close();
}
// [END vision_product_search_create_reference_image]
