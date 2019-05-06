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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 5 || count($argv) > 6) {
    return print("Usage: php deidentify_fpe.php CALLING_PROJECT STRING KEY_NAME WRAPPED_KEY [SURROGATE_TYPE_NAME]\n");
}
list($_, $callingProjectId, $string, $keyName, $wrappedKey) = $argv;
$surrogateTypeName = isset($argv[5]) ? $argv[5] : '';

# [START dlp_deidentify_fpe]
/**
 * Deidentify a string using Format-Preserving Encryption (FPE).
 */
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig\FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The GCP Project ID to run the API call under';
// $string = 'The string to deidentify';
// $keyName = 'The name of the Cloud KMS key used to encrypt (wrap) the AES-256 key';
// $wrappedKey = 'The name of the Cloud KMS key use, encrypted with the KMS key in $keyName';
// $surrogateTypeName = ''; // (Optional) surrogate custom info type to enable reidentification

// Instantiate a client.
$dlp = new DlpServiceClient();

// The infoTypes of information to mask
$ssnInfoType = (new InfoType())
    ->setName('US_SOCIAL_SECURITY_NUMBER');
$infoTypes = [$ssnInfoType];

// Create the wrapped crypto key configuration object
$kmsWrappedCryptoKey = (new KmsWrappedCryptoKey())
    ->setWrappedKey(base64_decode($wrappedKey))
    ->setCryptoKeyName($keyName);

// The set of characters to replace sensitive ones with
// For more information, see https://cloud.google.com/dlp/docs/reference/rest/V2/organizations.deidentifyTemplates#ffxcommonnativealphabet
$commonAlphabet = FfxCommonNativeAlphabet::NUMERIC;

// Create the crypto key configuration object
$cryptoKey = (new CryptoKey())
    ->setKmsWrapped($kmsWrappedCryptoKey);

// Create the crypto FFX FPE configuration object
$cryptoReplaceFfxFpeConfig = (new CryptoReplaceFfxFpeConfig())
    ->setCryptoKey($cryptoKey)
    ->setCommonAlphabet($commonAlphabet);

if ($surrogateTypeName) {
    $surrogateType = (new InfoType())
        ->setName($surrogateTypeName);
    $cryptoReplaceFfxFpeConfig->setSurrogateInfoType($surrogateType);
}

// Create the information transform configuration objects
$primitiveTransformation = (new PrimitiveTransformation())
    ->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

$infoTypeTransformation = (new InfoTypeTransformation())
    ->setPrimitiveTransformation($primitiveTransformation)
    ->setInfoTypes($infoTypes);

$infoTypeTransformations = (new InfoTypeTransformations())
    ->setTransformations([$infoTypeTransformation]);

// Create the deidentification configuration object
$deidentifyConfig = (new DeidentifyConfig())
    ->setInfoTypeTransformations($infoTypeTransformations);

$content = (new ContentItem())
    ->setValue($string);

$parent = $dlp->projectName($callingProjectId);

// Run request
$response = $dlp->deidentifyContent($parent, [
    'deidentifyConfig' => $deidentifyConfig,
    'item' => $content
]);

// Print the results
$deidentifiedValue = $response->getItem()->getValue();
print($deidentifiedValue);
# [END dlp_deidentify_fpe]
