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
use Google\Cloud\Dlp\V2beta1\DlpServiceClient;
use Google\Privacy\Dlp\V2beta1\CharacterMaskConfig;
use Google\Privacy\Dlp\V2beta1\ContentItem;
use Google\Privacy\Dlp\V2beta1\CryptoKey;
use Google\Privacy\Dlp\V2beta1\CryptoReplaceFfxFpeConfig;
use Google\Privacy\Dlp\V2beta1\CryptoReplaceFfxFpeConfig\FfxCommonNativeAlphabet;
use Google\Privacy\Dlp\V2beta1\DeidentifyConfig;
use Google\Privacy\Dlp\V2beta1\InfoType;
use Google\Privacy\Dlp\V2beta1\InfoTypeTransformations;
use Google\Privacy\Dlp\V2beta1\InfoTypeTransformations_InfoTypeTransformation;
use Google\Privacy\Dlp\V2beta1\InspectConfig;
use Google\Privacy\Dlp\V2beta1\KmsWrappedCryptoKey;
use Google\Privacy\Dlp\V2beta1\Likelihood;
use Google\Privacy\Dlp\V2beta1\PrimitiveTransformation;


/**
 * Deidentify a string with format-preserving encryption using the DLP API.
 *
 * @param string The string to deidentify.
 * @param alphabet The set of characters to use when encrypting the input. For more information, see cloud.google.com/dlp/docs/reference/rest/v2beta1/content/deidentify.
 * @param keyName The name of the Cloud KMS key to use when decrypting the wrapped key.
 * @param wrappedKey The encrypted (or "wrapped") AES-256 encryption key.
 */
function deidentify_fpe($string, $alphabet, $keyName, $wrappedKey)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Create the format-preserving encryption (FPE) configuration
    $kmsWrappedCryptoKey = new KmsWrappedCryptoKey();
    $kmsWrappedCryptoKey->setWrappedKey(base64_decode($wrappedKey));
    $kmsWrappedCryptoKey->setCryptoKeyName($keyName);

    $cryptoKey = new CryptoKey();
    $cryptoKey->setKmsWrapped($kmsWrappedCryptoKey);

    $cryptoReplaceFfxFpeConfig = new CryptoReplaceFfxFpeConfig();
    $cryptoReplaceFfxFpeConfig->setCryptoKey($cryptoKey);
    $cryptoReplaceFfxFpeConfig->setCommonAlphabet(4);

    $primitiveTransformation = new PrimitiveTransformation();
    $primitiveTransformation->setCryptoReplaceFfxFpeConfig($cryptoReplaceFfxFpeConfig);

    $infoTypeTransformation = new InfoTypeTransformations_InfoTypeTransformation();
    $infoTypeTransformation->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformationArray = new InfoTypeTransformations();
    $infoTypeTransformationArray->setTransformations([$infoTypeTransformation]);

    $deidentifyConfig = new DeidentifyConfig();
    $deidentifyConfig->setInfoTypeTransformations($infoTypeTransformationArray);

    $content = new ContentItem();
    $content->setType('text/plain');
    $content->setValue($string);

    // The infoTypes of information to match
    $usMaleNameInfoType = new InfoType();
    $usMaleNameInfoType->setName('US_MALE_NAME');
    $usFemaleNameInfoType = new InfoType();
    $usFemaleNameInfoType->setName('US_FEMALE_NAME');
    $infoTypes = [$usMaleNameInfoType, $usFemaleNameInfoType];

    // Create the inspectConfig object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setMinLikelihood(Likelihood::LIKELIHOOD_UNSPECIFIED);
    $inspectConfig->setMaxFindings(0);
    $inspectConfig->setInfoTypes($infoTypes);

    // Run request
    $response = $dlp->deidentifyContent($deidentifyConfig, $inspectConfig, [$content]);
    $content = $response->getItems()[0];

    // Print the results
    print('Deidentified string: ' . $content->getValue() . PHP_EOL);
}
# [END deidentify_fpe]
