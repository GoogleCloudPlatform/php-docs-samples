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

use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig\FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\SurrogateType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\ReidentifyContentRequest;
use Google\Cloud\Dlp\V2\UnwrappedCryptoKey;

# [START dlp_reidentify_free_text_with_fpe_using_surrogate]

/**
 * Re-identify free text with FPE using a surrogate.
 * Uses the Cloud Data Loss Prevention API to re-identify sensitive data in a string that was
 * encrypted by format-preserving encryption (FPE) with a surrogate type. The encryption is
 * performed with an unwrapped key.
 *
 * @param string $callingProjectId  The GCP Project ID to run the API call under.
 * @param string $string            The string to reidentify.
 * @param string $unwrappedKey      The base64-encoded AES-256 key to use.
 * @param string $surrogateTypeName The name of the surrogate custom info type to used during
 * the encryption process.
 */
function reidentify_free_text_with_fpe_using_surrogate(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $string,
    string $unwrappedKey = 'YWJjZGVmZ2hpamtsbW5vcA==',
    string $surrogateTypeName = 'PHONE_TOKEN'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Specify an unwrapped crypto key.
    $unwrapped = (new UnwrappedCryptoKey())
        ->setKey(base64_decode($unwrappedKey));

    $cryptoKey = (new CryptoKey())
        ->setUnwrapped($unwrapped);

    // Specify the surrogate type used at time of de-identification.
    $surrogateType = (new InfoType())
        ->setName($surrogateTypeName);

    // The set of characters to replace sensitive ones with.
    // For more information, see https://cloud.google.com/dlp/docs/reference/rest/V2/organizations.deidentifyTemplates#ffxcommonnativealphabet
    $commonAlphabet = FfxCommonNativeAlphabet::NUMERIC;

    // Specify how to decrypt the previously de-identified information.
    $cryptoReplaceFfxFpeConfig = (new CryptoReplaceFfxFpeConfig())
        ->setCryptoKey($cryptoKey)
        ->setCommonAlphabet($commonAlphabet)
        ->setSurrogateInfoType($surrogateType);

    // Create the information transform configuration objects.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the reidentification configuration object.
    $reidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Create the inspect configuration object.
    // Specify the type of info the inspection will look for.
    $infotype = (new InfoType())
        ->setName($surrogateTypeName);

    $customInfoType = (new CustomInfoType())
        ->setInfoType($infotype)
        ->setSurrogateType((new SurrogateType()));

    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType]);

    // Specify the content to be re-identify.
    $content = (new ContentItem())
        ->setValue($string);

    // Run request.
    $reidentifyContentRequest = (new ReidentifyContentRequest())
        ->setParent($parent)
        ->setReidentifyConfig($reidentifyConfig)
        ->setInspectConfig($inspectConfig)
        ->setItem($content);
    $response = $dlp->reidentifyContent($reidentifyContentRequest);

    // Print the results.
    printf($response->getItem()->getValue());
}
# [END dlp_reidentify_free_text_with_fpe_using_surrogate]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
