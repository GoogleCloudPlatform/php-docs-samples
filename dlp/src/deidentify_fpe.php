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

# [START deidentify_fpe]
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig;
use Google\Cloud\Dlp\V2\CryptoReplaceFfxFpeConfig_FfxCommonNativeAlphabet;
use Google\Cloud\Dlp\V2\CryptoKey;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\KmsWrappedCryptoKey;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations_InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;

/**
 * Deidentify a string using Format-Preserving Encryption (FPE) and the Data Loss Prevention (DLP) API.
 *
 * @param string $callingProject The GCP Project ID to run the API call under
 * @param string $string The string to deidentify
 * @param string $keyName The name of the Cloud KMS key used to encrypt ('wrap') the AES-256 key
 * @param wrappedKey $wrappedKey The AES-256 key to use, encrypted ('wrapped') with the KMS key
 *        defined by $keyName.
 * @param string $surrogateTypeName Optional surrogate custom info type to enable
 *        reidentification. Can be essentially any arbitrary string that doesn't
 *        appear in your dataset'
 */
function deidentify_fpe(
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

    // Create the wrapped crypto key configuration object
    $kmsWrappedCryptoKey = new KmsWrappedCryptoKey();
    $kmsWrappedCryptoKey->setWrappedKey(base64_decode($wrappedKey));
    $kmsWrappedCryptoKey->setCryptoKeyName($keyName);

    // The set of characters to replace sensitive ones with
    // For more information, see https://cloud.google.com/dlp/docs/reference/rest/V2/organizations.deidentifyTemplates#ffxcommonnativealphabet
    $commonAlphabet = CryptoReplaceFfxFpeConfig_FfxCommonNativeAlphabet::NUMERIC;

    // Create the crypto key configuration object
    $cryptoKey = new CryptoKey();
    $cryptoKey->setKmsWrapped($kmsWrappedCryptoKey);

    // Create the crypto FFX FPE configuration object
    $cryptoReplaceFfxFpeConfig = new CryptoReplaceFfxFpeConfig();
    $cryptoReplaceFfxFpeConfig->setCryptoKey($cryptoKey);
    $cryptoReplaceFfxFpeConfig->setCommonAlphabet($commonAlphabet);
    if ($surrogateTypeName) {
        $surrogateType = new InfoType();
        $surrogateType->setName($surrogateTypeName);
        $cryptoReplaceFfxFpeConfig->setSurrogateInfoType($surrogateType);
    }

    // Create the information transform configuration objects
    $primitiveTransformation = new PrimitiveTransformation();
    $primitiveTransformation->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

    $infoTypeTransformation = new InfoTypeTransformations_InfoTypeTransformation();
    $infoTypeTransformation->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = new InfoTypeTransformations();
    $infoTypeTransformations->setTransformations([$infoTypeTransformation]);

    // Create the deidentification configuration object
    $deidentifyConfig = new DeidentifyConfig();
    $deidentifyConfig->setInfoTypeTransformations($infoTypeTransformations);

    $content = new ContentItem();
    $content->setValue($string);

    $parent = $dlp->projectName($callingProject);

    // Run request
    $response = $dlp->deidentifyContent($parent, array(
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $content
    ));

    $likelihoods = ['Unknown', 'Very unlikely', 'Unlikely', 'Possible',
                    'Likely', 'Very likely'];

    // Print the results
    $deidentifiedValue = $response->getItem()->getValue();
    print_r($deidentifiedValue);
}
# [END deidentify_fpe]
