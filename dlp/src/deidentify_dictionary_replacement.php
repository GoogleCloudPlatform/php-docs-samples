<?php
/**
 * Copyright 2023 Google Inc.
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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_deidentify_dictionary_replacement]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\ReplaceDictionaryConfig;

/**
 * Dictionary replacement
 * Dictionary replacement (ReplaceDictionaryConfig) replaces each piece of detected sensitive data with a
 * value that Cloud DLP randomly selects from a list of words that you provide. This transformation method
 * is useful if you want to use realistic surrogate values.Suppose you want Cloud DLP to detect email addresses
 * and replace each detected value with one of three surrogate email addresses.
 *
 * @param string $callingProjectId  The project ID to run the API call under.
 * @param string $textToDeIdentify  The String you want the service to DeIdentify.
 */
function deidentify_dictionary_replacement(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $textToDeIdentify = 'My name is Charlie and email address is charlie@example.com.'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Specify what content you want the service to de-identify.
    $contentItem = (new ContentItem())
        ->setValue($textToDeIdentify);

    // Specify the type of info the inspection will look for.
    // See https://cloud.google.com/dlp/docs/infotypes-reference for complete list of info types
    $emailAddress = (new InfoType())
        ->setName('EMAIL_ADDRESS');

    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$emailAddress]);

    // Define type of de-identification as replacement with items from dictionary.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setReplaceDictionaryConfig(
            // Specify the dictionary to use for selecting replacement values for the finding.
            (new ReplaceDictionaryConfig())
                ->setWordList(
                    // Specify list of value which will randomly replace identified email addresses.
                    (new WordList())
                        ->setWords(['izumi@example.com', 'alex@example.com', 'tal@example.com'])
                )
        );

    $transformation = (new InfoTypeTransformation())
        ->setInfoTypes([$emailAddress])
        ->setPrimitiveTransformation($primitiveTransformation);

    // Construct the configuration for the de-identification request and list all desired transformations.
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations(
            (new InfoTypeTransformations())
                ->setTransformations([$transformation])
        );

    // Send the request and receive response from the service.
    $parent = "projects/$callingProjectId/locations/global";
    $deidentifyContentRequest = (new DeidentifyContentRequest())
        ->setParent($parent)
        ->setDeidentifyConfig($deidentifyConfig)
        ->setInspectConfig($inspectConfig)
        ->setItem($contentItem);
    $response = $dlp->deidentifyContent($deidentifyContentRequest);

    // Print the results.
    printf('Text after replace with infotype config: %s', $response->getItem()->getValue());
}
# [END dlp_deidentify_dictionary_replacement]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
