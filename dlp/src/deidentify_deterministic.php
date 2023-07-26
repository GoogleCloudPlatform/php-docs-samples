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

# [START dlp_deidentify_deterministic]

use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CryptoDeterministicConfig;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;

/**
 * De-identify content through deterministic encryption.
 * Use the Data Loss Prevention API to de-identify sensitive data in a string using deterministic encryption,
 * which is a reversible cryptographic method. The encryption is performed with a wrapped key.
 *
 * @param string $callingProjectId  The Google Cloud project id to use as a parent resource.
 * @param string $inputString       The string to deidentify (will be treated as text).
 * @param string $kmsKeyName        The name of the Cloud KMS key used to encrypt ('wrap') the AES-256 key.
 * Example: key_name = 'projects/YOUR_GCLOUD_PROJECT/locations/YOUR_LOCATION/keyRings/YOUR_KEYRING_NAME/cryptoKeys/YOUR_KEY_NAME'.
 * @param string $infoTypeName      The Info type name to be inspect.
 * @param string $surrogateType     The name of the surrogate custom info type to use.
 * Only necessary if you want to reverse the deidentification process. Can be essentially any arbitrary
 * string, as long as it doesn't appear in your dataset otherwise.
 * @param string $wrappedAesKey     The encrypted ('wrapped') AES-256 key to use.
 *
 * */

function deidentify_deterministic(
    string $callingProjectId,
    string $kmsKeyName,
    string $wrappedAesKey,
    string $inputString = 'My PHONE NUMBER IS 731997681',
    string $infoTypeName = 'PHONE_NUMBER',
    string $surrogateTypeName = 'PHONE_TOKEN'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Specify what content you want the service to DeIdentify.
    $content = (new ContentItem())->setValue($inputString);

    // Specify an encrypted AES-256 key and the name of the Cloud KMS key that encrypted it.
    $kmsWrappedCryptoKey = (new KmsWrappedCryptoKey())
        ->setWrappedKey(base64_decode($wrappedAesKey))
        ->setCryptoKeyName($kmsKeyName);

    $cryptoKey = (new CryptoKey())
        ->setKmsWrapped($kmsWrappedCryptoKey);

    // Specify how the info from the inspection should be encrypted.
    $cryptoDeterministicConfig = (new CryptoDeterministicConfig())
        ->setCryptoKey($cryptoKey);

    if (!empty($surrogateTypeName)) {
        $cryptoDeterministicConfig = $cryptoDeterministicConfig->setSurrogateInfoType((new InfoType())
            ->setName($surrogateTypeName));
    }

    // Specify the type of info the inspection will look for.
    $infoType = (new InfoType())
        ->setName($infoTypeName);

    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$infoType]);

    $primitiveTransformation = (new PrimitiveTransformation())
        ->setCryptoDeterministicConfig($cryptoDeterministicConfig);

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Send the request and receive response from the service.
    $deidentifyContentRequest = (new DeidentifyContentRequest())
        ->setParent($parent)
        ->setDeidentifyConfig($deidentifyConfig)
        ->setItem($content)
        ->setInspectConfig($inspectConfig);
    $response = $dlp->deidentifyContent($deidentifyContentRequest);

    // Print the results.
    printf($response->getItem()->getValue());
}
# [END dlp_deidentify_deterministic]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
