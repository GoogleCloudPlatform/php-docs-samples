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

require_once __DIR__ . '/../vendor/autoload.php';

use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\DeleteTemplateRequest;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use Google\Cloud\ModelArmor\V1\DetectionConfidenceLevel;
use Google\Cloud\ModelArmor\V1\Template;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\SdpAdvancedConfig;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings\PiAndJailbreakFilterEnforcement;
use Google\Cloud\ModelArmor\V1\MaliciousUriFilterSettings\MaliciousUriFilterEnforcement;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings;
use Google\Cloud\ModelArmor\V1\MaliciousUriFilterSettings;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeleteDeidentifyTemplateRequest;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\ModelArmor\V1\CreateTemplateRequest;
use Google\Cloud\Dlp\V2\Value;
use Google\Cloud\Dlp\V2\ReplaceValueConfig;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\DeleteInspectTemplateRequest;
use Google\Cloud\Dlp\V2\CreateInspectTemplateRequest;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\DeidentifyTemplate;
use Google\Cloud\Dlp\V2\CreateDeidentifyTemplateRequest;
use Google\Cloud\Dlp\V2\InspectTemplate;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\ModelArmor\V1\SdpBasicConfig\SdpBasicConfigEnforcement;
use Google\Cloud\ModelArmor\V1\SdpBasicConfig;
use Google\Cloud\ModelArmor\V1\SdpFilterSettings;
use Google\Cloud\ModelArmor\V1\RaiFilterType;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings\RaiFilter;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;

abstract class BaseSanitizeTestCase extends TestCase
{
    use TestTrait;
    protected static $client;
    protected static $templateId;
    protected static $locationId = 'us-central1';
    protected static $templatesToDelete = [];
    protected static $templateIdPrefix;
    protected static $dlpClient;
    protected static $testMaliciousTemplateId;
    protected static $testPIandJailbreakTemplateId;
    protected static $testbasicSdpTemplateId;
    protected static $testAdvanceSdpTemplateId;
    protected static $testRaiTemplateId;
    protected static $inspectTemplateName = '';
    protected static $deidentifyTemplateName = '';

    public static function setUpBeforeClass(): void
    {
        $options = ['apiEndpoint' => 'modelarmor.' . self::$locationId . '.rep.googleapis.com'];
        self::$client = new ModelArmorClient($options);
        self::$dlpClient = new DlpServiceClient([
            'apiEndpoint' => 'dlp.' . self::$locationId . '.rep.googleapis.com'
        ]);
        self::$templateId = static::getTemplatePrefix() . uniqid();
        self::$templateIdPrefix = 'test-template-' . substr(uniqid(), 0, 8);
        self::createTemplates();
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$templatesToDelete as $templateName) {
            self::deleteTemplate($templateName);
        }
        self::deleteDlpTemplates(self::$inspectTemplateName, self::$deidentifyTemplateName, self::$locationId);
    }

    abstract protected static function getTemplatePrefix(): string;

    protected function runSnippetfile(string $snippetName, array $params = []): string
    {
        $output = $this->runSnippet($snippetName, $params);
        return $output;
    }

    protected static function getProjectId()
    {
        return self::$projectId;
    }

    protected static function deleteTemplate($templateName)
    {
        try {
            $request = (new DeleteTemplateRequest())->setName($templateName);
            self::$client->deleteTemplate($request);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    protected static function createTemplate($templateId, $template)
    {
        $parent = self::$client->locationName(self::$projectId, self::$locationId);

        $request = (new CreateTemplateRequest)
            ->setParent($parent)
            ->setTemplateId($templateId)
            ->setTemplate($template);
        try {
            $response = self::$client->createTemplate($request);
            return $response;
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    protected static function deleteDlpTemplates(string $inspectTemplateName, string $deidentifyTemplateName, string $locationId): void
    {
        try {
            // Delete inspect template.
            if ($inspectTemplateName) {
                $dlpDltInspectRequest = (new DeleteInspectTemplateRequest())->setName($inspectTemplateName);
                self::$dlpClient->deleteInspectTemplate($dlpDltInspectRequest);
            }

            // Delete deidentify template.
            if ($deidentifyTemplateName) {
                $dlpDltDeIndetifyRequest = (new DeleteDeidentifyTemplateRequest())->setName($deidentifyTemplateName);
                self::$dlpClient->deleteDeidentifyTemplate($dlpDltDeIndetifyRequest);
            }
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    protected static function createTemplates()
    {
        // Create basic template with PI/Jailbreak filters for sanitizeUserPrompt tests.
        self::$testPIandJailbreakTemplateId = self::$templateIdPrefix . '-pi';
        $templateFilterConfig = (new FilterConfig())
            ->setPiAndJailbreakFilterSettings((new PiAndJailbreakFilterSettings())
                ->setFilterEnforcement(PiAndJailbreakFilterEnforcement::ENABLED)
                ->setConfidenceLevel(DetectionConfidenceLevel::MEDIUM_AND_ABOVE));
        $template = (new Template())->setFilterConfig($templateFilterConfig);
        self::createTemplate(self::$testPIandJailbreakTemplateId, $template);
        self::$templatesToDelete[] = 'projects/' . self::$projectId . '/locations/' . self::$locationId . '/templates/' . self::$testPIandJailbreakTemplateId;

        // Create Malicious URI filters for sanitizeUserPrompt tests.
        self::$testMaliciousTemplateId = self::$templateIdPrefix . '-malicious';
        $templateFilterConfig = (new FilterConfig())
            ->setMaliciousUriFilterSettings((new MaliciousUriFilterSettings())
                ->setFilterEnforcement(MaliciousUriFilterEnforcement::ENABLED));
        $template = (new Template())->setFilterConfig($templateFilterConfig);
        self::createTemplate(self::$testMaliciousTemplateId, $template);
        self::$templatesToDelete[] = 'projects/' . self::$projectId . '/locations/' . self::$locationId . '/templates/' . self::$testMaliciousTemplateId;

        // Create basic SDP template.
        self::$testbasicSdpTemplateId = self::$templateIdPrefix . '-basic-sdp';
        $sdpBasicConfig = (new SdpBasicConfig())
            ->setFilterEnforcement(SdpBasicConfigEnforcement::ENABLED);

        $sdpSettings = (new SdpFilterSettings())->setBasicConfig($sdpBasicConfig);

        $templateFilterConfig = (new FilterConfig())
            ->setSdpSettings($sdpSettings);

        $template = (new Template())->setFilterConfig($templateFilterConfig);
        self::createTemplate(self::$testbasicSdpTemplateId, $template);
        self::$templatesToDelete[] = 'projects/' . self::$projectId . '/locations/' . self::$locationId . '/templates/' . self::$testbasicSdpTemplateId;

        // Generate unique template IDs.
        $inspectTemplateId = 'model-armor-inspect-template-' . uniqid();
        $deidentifyTemplateId = 'model-armor-deidentify-template-' . uniqid();
        $parent = self::$dlpClient->locationName(self::$projectId, self::$locationId);

        $inspectConfig = (new InspectConfig())
            ->setInfoTypes([
                (new InfoType())->setName('EMAIL_ADDRESS'),
                (new InfoType())->setName('PHONE_NUMBER'),
                (new InfoType())->setName('US_INDIVIDUAL_TAXPAYER_IDENTIFICATION_NUMBER'),
            ]);
        $inspectTemplate = (new InspectTemplate())
            ->setInspectConfig($inspectConfig);
        $inspectTemplateRequest = (new CreateInspectTemplateRequest())
            ->setParent($parent)
            ->setTemplateId($inspectTemplateId)
            ->setInspectTemplate($inspectTemplate);

        // Create inspect template.
        $inspectTemplateResponse = self::$dlpClient->createInspectTemplate($inspectTemplateRequest);
        self::$inspectTemplateName = $inspectTemplateResponse->getName();

        $replaceValueConfig = (new ReplaceValueConfig())->setNewValue((new Value())->setStringValue('[REDACTED]'));
        $primitiveTrasformation = (new PrimitiveTransformation())->setReplaceConfig($replaceValueConfig);
        $transformations = (new InfoTypeTransformation())
            ->setInfoTypes([])
            ->setPrimitiveTransformation($primitiveTrasformation);

        $infoTypeTransformations = (new InfoTypeTransformations())
            ->setTransformations([$transformations]);
        $deidentifyconfig = (new DeidentifyConfig())->setInfoTypeTransformations($infoTypeTransformations);
        $deidentifyTemplate = (new DeidentifyTemplate())->setDeidentifyConfig($deidentifyconfig);
        $deidentifyTemplateRequest = (new CreateDeidentifyTemplateRequest())
            ->setParent($parent)
            ->setTemplateId($deidentifyTemplateId)
            ->setDeidentifyTemplate($deidentifyTemplate);

        // Create deidentify template.
        $deidentifyTemplateResponse = self::$dlpClient->createDeidentifyTemplate($deidentifyTemplateRequest);
        self::$deidentifyTemplateName = $deidentifyTemplateResponse->getName();

        self::$testAdvanceSdpTemplateId = self::$templateIdPrefix . '-advanced-sdp';

        $sdpAdvancedConfig = (new SdpAdvancedConfig())
            ->setInspectTemplate(self::$inspectTemplateName)
            ->setDeidentifyTemplate(self::$deidentifyTemplateName);

        $sdpSettings = (new SdpFilterSettings())->setAdvancedConfig($sdpAdvancedConfig);

        $templateFilterConfig = (new FilterConfig())
            ->setSdpSettings($sdpSettings);

        $template = (new Template())->setFilterConfig($templateFilterConfig);

        self::createTemplate(self::$testAdvanceSdpTemplateId, $template);

        self::$templatesToDelete[] = 'projects/' . self::$projectId . '/locations/' . self::$locationId . '/templates/' . self::$testAdvanceSdpTemplateId;

        // Create template with RAI filters.
        self::$testRaiTemplateId = self::$templateIdPrefix . '-raiFilters';
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

        $raiFilterSetting = (new RaiFilterSettings())->setRaiFilters($raiFilters);

        $templateFilterConfig = (new FilterConfig())->setRaiSettings($raiFilterSetting);

        $template = (new Template())->setFilterConfig($templateFilterConfig);

        self::createTemplate(self::$testRaiTemplateId, $template);
        self::$templatesToDelete[] = 'projects/' . self::$projectId . '/locations/' . self::$locationId . '/templates/' . self::$testRaiTemplateId;
    }
}
