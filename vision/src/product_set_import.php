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

// [START vision_product_search_import_product_images]
namespace Google\Cloud\Samples\Vision;

// [START vision_product_search_tutorial_import]
use Google\Cloud\Vision\V1\ProductSearchClient;
use Google\Cloud\Vision\V1\ImportProductSetsGcsSource;
use Google\Cloud\Vision\V1\ImportProductSetsInputConfig;

// [END vision_product_search_tutorial_import]

/**
 * Import images of different products in the product set.
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $location Google Cloud compute region name
 * @param string $gcsUri Google Cloud Storage URI
 */
function product_set_import($projectId, $location, $gcsUri)
{
    $client = new ProductSearchClient();

    # a resource that represents Google Cloud Platform location.
    $locationPath = $client->locationName($projectId, $location);

    # set the input configuration along with Google Cloud Storage URI
    $gcsSource = (new ImportProductSetsGcsSource())
        ->setCsvFileUri($gcsUri);
    $inputConfig = (new ImportProductSetsInputConfig())
        ->setGcsSource($gcsSource);

    # import the product sets from the input URI
    $operation = $client->importProductSets($locationPath, $inputConfig);
    $operationName = $operation->getName();
    printf('Processing operation name: %s' . PHP_EOL, $operationName);

    $operation->pollUntilComplete();
    print('Processing done.' . PHP_EOL);

    if ($result = $operation->getResult()) {
        $referenceImages = $result->getReferenceImages();

        foreach ($result->getStatuses() as $count => $status) {
            printf('Status of processing line %d of the csv: ' . PHP_EOL, $count);
            # check the status of reference image
            # `0` is the code for OK in google.rpc.Code.
            if ($status->getCode() == 0) {
                $referenceImage = $referenceImages[$count];
                printf('name: %s' . PHP_EOL, $referenceImage->getName());
                printf('uri: %s' . PHP_EOL, $referenceImage->getUri());
            } else {
                printf('Status code not OK: %s' . PHP_EOL, $status->getMessage());
            }
        }
        print('IMPORTANT: You will need to wait up to 30 minutes for indexing to complete' . PHP_EOL);
    } else {
        printf('Error: %s' . PHP_EOL, $operation->getError()->getMessage());
    }

    $client->close();
}
// [END vision_product_search_import_product_images]
