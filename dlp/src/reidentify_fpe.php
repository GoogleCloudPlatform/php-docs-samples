<?php

/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Samples\Dlp;

# [START reidentify_fpe]
use Google\Cloud\Dlp\V2beta2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2beta2\CryptoReplaceFfxFpeConfig_FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2beta2\CryptoKey;
use Google\Cloud\Dlp\V2beta2\DlpServiceClient;
use Google\Cloud\Dlp\V2beta2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2beta2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2beta2\CharacterMaskConfig;
use Google\Cloud\Dlp\V2beta2\InfoType;
use Google\Cloud\Dlp\V2beta2\ReidentifyConfig;
use Google\Cloud\Dlp\V2beta2\InspectConfig;
use Google\Cloud\Dlp\V2beta2\InfoTypeTransformations_InfoTypeTransformation;
use Google\Cloud\Dlp\V2beta2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2beta2\ContentItem;

/**
 * Inspect a string using Format-Preserving Encryption (FPE) and the Data Loss Prevention (DLP) API.
 *
 * @param string $callingProject The GCP Project ID to run the API call under
 * @param string $string The string to deidentify
 * @param keyName $keyName The name of the Cloud KMS key used to encrypt ('wrap') the AES-256 key
 * @param string $wrappedKey The AES-256 key to use, encrypted ('wrapped') with the KMS key
 *        defined by $keyName.
 * @param string $surrogateTypeName Optional surrogate custom info type to enable
 *        reidentification. Can be essentially any arbitrary string that doesn't
 *        appear in your dataset'
 */
function reidentify_fpe(
    $callingProject,
    $string,
    $keyName,
    $wrappedKey,
    $surrogateTypeName = '')
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to mask
    $ssnInfoType = new InfoType();
    $ssnInfoType->setName('US_SOCIAL_SECURITY_NUMBER');
    $infoTypes = [$ssnInfoType];

    // The set of characters to replace sensitive ones with
    // For more information, see https://cloud.google.com/dlp/docs/reference/rest/v2beta2/organizations.deidentifyTemplates#ffxcommonnativealphabet
    $commonAlphabet = CryptoReplaceFfxFpeConfig_FfxCommonNativeAlphabet::NUMERIC;

    // Create the wrapped crypto key configuration object
    $kmsWrappedCryptoKey = new KmsWrappedCryptoKey();
    $kmsWrappedCryptoKey->setWrappedKey($wrappedKey);
    $kmsWrappedCryptoKey->setCryptoKeyName($keyName);

    // Create the crypto key configuration object
    $cryptoKey = new CryptoKey();
    $cryptoKey->setKmsWrappedCryptoKey($kmsWrappedCryptoKey);

    // Create the surrogate type object
    $surrogateType = new InfoType();
    $surrogateType->setName($surrogateTypeName)

    // Create the crypto FFX FPE configuration object
    $cryptoReplaceFfxFpeConfig = new CryptoReplaceFfxFpeConfig();
    $cryptoReplaceFfxFpeConfig->setCryptoKey($cryptoKey);
    $cryptoReplaceFfxFpeConfig->setCommonAlphabet($commonAlphabet);
    $cryptoReplaceFfxFpeConfig->setSurrogateInfoType($surrogateType);

    // Create the information transform configuration objects
    $primitiveTransformation = new PrimitiveTransformation();
    $primitiveTransformation->setInfoTypes([$surrogateType])
    $primitiveTransformation->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

    $infoTypeTransformation = new InfoTypeTransformations_InfoTypeTransformation();
    $infoTypeTransformation->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = new InfoTypeTransformations();
    $infoTypeTransformations->setTransformations([$infoTypeTransformation]);

    // Create the inspect configuration object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setCustomInfoTypes([$surrogateType]);

    // Create the reidentification configuration object
    $reidentifyConfig = new ReidentifyConfig();
    $reidentifyConfig->setInfoTypeTransformations($infoTypeTransformations);

    $content = new ContentItem();
    $content->setType('text/plain');
    $content->setValue($string);

    $parent = $dlp->projectName($callingProject);

    // Run request
    $response = $dlp->reidentifyContent($parent, Array(
        'reidentifyConfig' => $reidentifyConfig,
        'inspectConfig' => $inspectConfig,
        'item' => $item
    ));

    $likelihoods = ['Unknown', 'Very unlikely', 'Unlikely', 'Possible',
                    'Likely', 'Very likely'];

    // Print the results
    $reidentifiedValue = $response->getItem()->getValue();
    print_r($reidentifiedValue);
}
# [END reidentify_fpe]