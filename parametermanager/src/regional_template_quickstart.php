<?php
/*
 * Copyright 2025 Google LLC.
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

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 6) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID TEMPLATE_ID PARAMETER_ID VERSION_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $templateId, $parameterId, $versionId) = $argv;

// [START parametermanager_regional_template_quickstart]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterRequest;
use Google\Cloud\ParameterManager\V1\CreateParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\CreateTemplateRequest;
use Google\Cloud\ParameterManager\V1\CreateTemplateVersionRequest;
use Google\Cloud\ParameterManager\V1\Parameter;
use Google\Cloud\ParameterManager\V1\ParameterFormat;
use Google\Cloud\ParameterManager\V1\ParameterVersion;
use Google\Cloud\ParameterManager\V1\ParameterVersionPayload;
use Google\Cloud\ParameterManager\V1\RenderTemplateVersionRequest;
use Google\Cloud\ParameterManager\V1\Template;
use Google\Cloud\ParameterManager\V1\TemplateFormat;
use Google\Cloud\ParameterManager\V1\TemplateVersion;
use Google\Cloud\ParameterManager\V1\TemplateVersionPayload;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_GOOGLE_CLOUD_PROJECT' (e.g. 'my-project');
// $locationId = 'YOUR_LOCATION_ID' (e.g. 'us-central1');
// $templateId = 'YOUR_TEMPLATE_ID' (e.g. 'my-template');
// $parameterId = 'YOUR_PARAMETER_ID' (e.g. 'my-param');
// $versionId = 'YOUR_VERSION_ID' (e.g. 'my-version');

// Specify regional endpoint.
$options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

// Create a client for the Parameter Manager service.
$client = new ParameterManagerClient($options);

// Build the resource name of the parent object.
$parent = $client->locationName($projectId, $locationId);

// Create a template in JSON format.
$template = $client->createTemplate((new CreateTemplateRequest())
    ->setParent($parent)
    ->setTemplateId($templateId)
    ->setTemplate((new Template())->setFormat(TemplateFormat::TEMPLATE_FORMAT_JSON)));
printf('Created regional template: %s' . PHP_EOL, $template->getName());

// Create a template version with {{.variableName}} placeholders.
$templatePayload = json_encode(['username' => '{{.username}}', 'host' => '{{.host}}']);
$templateVersion = $client->createTemplateVersion((new CreateTemplateVersionRequest())
    ->setParent($template->getName())
    ->setTemplateVersionId($versionId)
    ->setTemplateVersion((new TemplateVersion())
        ->setPayload((new TemplateVersionPayload())->setData($templatePayload))));
printf('Created regional template version: %s' . PHP_EOL, $templateVersion->getName());

// Create a JSON parameter.
$parameter = $client->createParameter((new CreateParameterRequest())
    ->setParent($parent)
    ->setParameterId($parameterId)
    ->setParameter((new Parameter())->setFormat(ParameterFormat::JSON)));
printf('Created regional parameter: %s' . PHP_EOL, $parameter->getName());

// Create a parameter version that holds the values for the placeholders.
$values = json_encode(['username' => 'test-user', 'host' => 'localhost']);
$parameterVersion = $client->createParameterVersion((new CreateParameterVersionRequest())
    ->setParent($parameter->getName())
    ->setParameterVersionId($versionId)
    ->setParameterVersion((new ParameterVersion())
        ->setPayload((new ParameterVersionPayload())->setData($values))));
printf('Created regional parameter version: %s' . PHP_EOL, $parameterVersion->getName());

// Render the template version with the parameter version's values.
$response = $client->renderTemplateVersion((new RenderTemplateVersionRequest())
    ->setName($templateVersion->getName())
    ->setParameterVersion($parameterVersion->getName()));
printf('Rendered payload: %s' . PHP_EOL, $response->getRenderedPayload());
// [END parametermanager_regional_template_quickstart]
