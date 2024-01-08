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

# [START dlp_reidentify_deterministic]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CryptoDeterministicConfig;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\SurrogateType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\ReidentifyContentRequest;

/**
 * Re-identify content encrypted by deterministic encryption.
 * Re-identify content that was previously de-identified through deterministic encryption.
 *
 * @param string $callingProjectId  The GCP Project ID to run the API call under.
 * @param string $string            The string to reidentify.
 * @param string $keyName           The name of the Cloud KMS key used to encrypt (wrap) the AES-256 key.
 * @param string $wrappedKey        The name of the Cloud KMS key use, encrypted with the KMS key in $keyName.
 * @param string $surrogateTypeName Surrogate custom info type to enable reidentification.
 */
function reidentify_deterministic(
    string $callingProjectId,
    string $string,
    string $surrogateTypeName,
    string $keyName,
    string $wrappedKey
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Specify what content you want the service to Re-identify.
    $item = (new ContentItem())
        ->setValue($string);

    // Specify the surrogate type used at time of de-identification.
    $surrogateType = (new InfoType())
        ->setName($surrogateTypeName);

    $customInfoType = (new CustomInfoType())
        ->setInfoType($surrogateType)
        ->setSurrogateType(new SurrogateType());

    // Create the inspect configuration object.
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType]);

    // Specify an encrypted AES-256 key and the name of the Cloud KMS key that encrypted it.
    $kmsWrappedCryptoKey = (new KmsWrappedCryptoKey())
        ->setWrappedKey(base64_decode($wrappedKey))
        ->setCryptoKeyName($keyName);

    // Create the crypto key configuration object.
    $cryptoKey = (new CryptoKey())
        ->setKmsWrapped($kmsWrappedCryptoKey);

    // Create the crypto deterministic configuration object.
    $cryptoDeterministicConfig = (new CryptoDeterministicConfig())
        ->setCryptoKey($cryptoKey)
        ->setSurrogateInfoType($surrogateType);

    // Create the information transform configuration objects.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setCryptoDeterministicConfig($cryptoDeterministicConfig);

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the reidentification configuration object.
    $reidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Run request.
    $reidentifyContentRequest = (new ReidentifyContentRequest())
        ->setParent($parent)
        ->setReidentifyConfig($reidentifyConfig)
        ->setInspectConfig($inspectConfig)
        ->setItem($item);
    $response = $dlp->reidentifyContent($reidentifyContentRequest);

    // Print the results.
    printf($response->getItem()->getValue());
}
# [END dlp_reidentify_deterministic]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
