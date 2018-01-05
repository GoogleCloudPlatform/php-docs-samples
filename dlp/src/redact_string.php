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

# [START redact_string]
use Google\Cloud\Dlp\V2beta1\DlpServiceClient;
use Google\Cloud\Dlp\V2beta1\ContentItem;
use Google\Cloud\Dlp\V2beta1\InfoType;
use Google\Cloud\Dlp\V2beta1\InspectConfig;
use Google\Cloud\Dlp\V2beta1\RedactContentRequest_ReplaceConfig;
use Google\Cloud\Dlp\V2beta1\Likelihood;

/**
 * Redact a sensitive string using the Data Loss Prevention (DLP) API.
 *
 * @param string $string The text to inspect
 */
function redact_string(
    $string,
    $replaceString,
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED,
    $maxFindings = 0)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to match
    $usMaleNameInfoType = new InfoType();
    $usMaleNameInfoType->setName('US_MALE_NAME');
    $usFemaleNameInfoType = new InfoType();
    $usFemaleNameInfoType->setName('US_FEMALE_NAME');
    $infoTypes = [$usMaleNameInfoType, $usFemaleNameInfoType];

    // Whether to include the matching string in the response
    $includeQuote = true;

    // Create the configuration object
    $inspectConfig = new InspectConfig();
    $inspectConfig->setMinLikelihood($minLikelihood);
    $inspectConfig->setMaxFindings($maxFindings);
    $inspectConfig->setInfoTypes($infoTypes);
    $inspectConfig->setIncludeQuote($includeQuote);

    $content = new ContentItem();
    $content->setType('text/plain');
    $content->setValue($string);

    $redactConfigs = [];
    foreach ($infoTypes as $infoType) {
        $redactConfig = new RedactContentRequest_ReplaceConfig();
        $redactConfig->setInfoType($infoType);
        $redactConfig->setReplaceWith($replaceString);
        $redactConfigs[] = $redactConfig;
    }

    // Run request
    $response = $dlp->redactContent(
        $inspectConfig,
        [$content],
        ['replaceConfigs' => $redactConfigs]
    );
    $content = $response->getItems()[0];

    // Print the results
    print('Redacted String: ' . $content->getValue() . PHP_EOL);
}
# [END redact_string]
