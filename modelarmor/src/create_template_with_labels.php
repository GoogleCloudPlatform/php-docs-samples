<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

declare(strict_types=1);

namespace Google\Cloud\Samples\ModelArmor;

// [START modelarmor_create_template_with_labels]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\Template;
use Google\Cloud\ModelArmor\V1\CreateTemplateRequest;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings\RaiFilter;
use Google\Cloud\ModelArmor\V1\RaiFilterType;
use Google\Cloud\ModelArmor\V1\DetectionConfidenceLevel;

/**
 * Create a Model Armor template with labels.
 *
 * @param string $projectId The ID of the project (e.g. 'my-project').
 * @param string $locationId The ID of the location (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 * @param string $labelKey The key of the label to add. (e.g. 'my-label-key').
 * @param string $labelValue The value of the label to add. (e.g. 'my-label-value').
 */
function create_template_with_labels(
    string $projectId,
    string $locationId,
    string $templateId,
    string $labelKey,
    string $labelValue
): void
{
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);
    $parent = $client->locationName($projectId, $locationId);

    $raiFilters = [
        (new RaiFilter())
            ->setFilterType(RaiFilterType::DANGEROUS)
            ->setConfidenceLevel(DetectionConfidenceLevel::HIGH),
        (new RaiFilter())
            ->setFilterType(RaiFilterType::HATE_SPEECH)
            ->setConfidenceLevel(DetectionConfidenceLevel::HIGH),
        (new RaiFilter())
            ->setFilterType(RaiFilterType::SEXUALLY_EXPLICIT)
            ->setConfidenceLevel(DetectionConfidenceLevel::LOW_AND_ABOVE),
        (new RaiFilter())
            ->setFilterType(RaiFilterType::HARASSMENT)
            ->setConfidenceLevel(DetectionConfidenceLevel::MEDIUM_AND_ABOVE),
    ];

    $raiSettings = (new RaiFilterSettings())->setRaiFilters($raiFilters);
    $filterConfig = (new FilterConfig())->setRaiSettings($raiSettings);

    // Build template with filters and labels.
    $template = (new Template())
        ->setFilterConfig($filterConfig)
        ->setLabels([$labelKey => $labelValue]);

    $request = (new CreateTemplateRequest())
        ->setParent($parent)
        ->setTemplateId($templateId)
        ->setTemplate($template);

    $response = $client->createTemplate($request);

    printf('Template created: %s' . PHP_EOL, $response->getName());
}
// [END modelarmor_create_template_with_labels]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
