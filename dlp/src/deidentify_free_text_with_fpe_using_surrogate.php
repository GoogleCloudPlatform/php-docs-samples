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

use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig\FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\UnwrappedCryptoKey;

# [START dlp_deidentify_free_text_with_fpe_using_surrogate]

/**
 * Deidentify free text with fpe using surrogate.
 * Uses the Data Loss Prevention API to de-identify sensitive data in a string using format-preserving
 * encryption (FPE). The encryption is performed with an unwrapped key.
 *
 * @param string $callingProjectId  The GCP Project ID to run the API call under.
 * @param string $string            The string to deidentify (will be treated as text).
 * @param string $unwrappedKey      The base64-encoded AES-256 key to use.
 * @param string $surrogateTypeName The name of the surrogate custom info type to use.
 * Can be essentially any arbitrary string, as long as it doesn't appear in your dataset otherwise.
 */
function deidentify_free_text_with_fpe_using_surrogate(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $string = 'My PHONE NUMBER IS 731997681',
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

    // Create the surrogate type configuration object.
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

    // The infoTypes of information to mask.
    $infoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infoTypes = [$infoType];

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation)
        ->setInfoTypes($infoTypes);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the deidentification configuration object.
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Specify the content to be de-identify.
    $content = (new ContentItem())
        ->setValue($string);

    // Create the configuration object.
    $inspectConfig = (new InspectConfig())
        /* Construct the inspect config, trying to finding all PII with likelihood
        higher than UNLIKELY */
        ->setMinLikelihood(likelihood::UNLIKELY)
        ->setInfoTypes($infoTypes);

    // Run request.
    $response = $dlp->deidentifyContent([
        'parent' => $parent,
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $content,
        'inspectConfig' => $inspectConfig
    ]);

    // Print the results.
    printf($response->getItem()->getValue());
}
# [END dlp_deidentify_free_text_with_fpe_using_surrogate]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
