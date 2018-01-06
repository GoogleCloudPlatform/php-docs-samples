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

# [START deidentify_masking]
use Google\Cloud\Dlp\V2beta1\DlpServiceClient;
use Google\Privacy\Dlp\V2beta1\CharacterMaskConfig;
use Google\Privacy\Dlp\V2beta1\ContentItem;
use Google\Privacy\Dlp\V2beta1\DeidentifyConfig;
use Google\Privacy\Dlp\V2beta1\InfoType;
use Google\Privacy\Dlp\V2beta1\InfoTypeTransformations;
use Google\Privacy\Dlp\V2beta1\InfoTypeTransformations_InfoTypeTransformation;
use Google\Privacy\Dlp\V2beta1\InspectConfig;
use Google\Privacy\Dlp\V2beta1\Likelihood;
use Google\Privacy\Dlp\V2beta1\PrimitiveTransformation;


/**
 * Deidentify a string by masking sensitive information with a character using the DLP API.
 *
 * @param string The string to deidentify.
 * @param maskingCharacter (Optional) The character to mask sensitive data with.
 * @param numberToMask (Optional) The number of characters' worth of sensitive data to mask.
 * Omitting this value or setting it to 0 masks all sensitive characters.
 */
function deidentify_masking($string, $maskingCharacter, $numberToMask = 0)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $characterMaskConfig = new CharacterMaskConfig();
    $characterMaskConfig->setMaskingCharacter($maskingCharacter);
    $characterMaskConfig->setNumberToMask($numberToMask);

    $primitiveTransformation = new PrimitiveTransformation();
    $primitiveTransformation->setCharacterMaskConfig($characterMaskConfig);

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
# [END deidentify_masking]
