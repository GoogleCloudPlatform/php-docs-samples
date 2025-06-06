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

use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeleteDeidentifyTemplateRequest;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\Value;
use Google\Cloud\Dlp\V2\ReplaceValueConfig;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\DeleteInspectTemplateRequest;
use Google\Cloud\Dlp\V2\CreateInspectTemplateRequest;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\DeidentifyTemplate;
use Google\Cloud\Dlp\V2\CreateDeidentifyTemplateRequest;
use Google\Cloud\Dlp\V2\InspectTemplate;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;

class createTemplateWithAdvancedSdpTest extends BaseTestCase
{
    private static $inspectTemplateName = '';
    private static $deidentifyTemplateName = '';

    protected static function getTemplatePrefix(): string
    {
        return 'php-template-advanced-sdp-';
    }

    protected static function customTeardown(): void
    {
        self::delete_dlp_templates(self::$inspectTemplateName, self::$deidentifyTemplateName, self::$locationId);
    }

    public function testCreateTemplateWithAdvancedSdp()
    {
        $projectId = self::getProjectId();
        $templates = self::create_dlp_templates($projectId, self::$locationId);
        self::$inspectTemplateName = $templates['inspectTemplateName'];
        self::$deidentifyTemplateName = $templates['deidentifyTemplateName'];
        $output = $this->runSnippetfile('create_template_with_advanced_sdp', [
            $projectId,
            self::$locationId,
            self::$templateId,
            self::$inspectTemplateName,
            self::$deidentifyTemplateName,
        ]);

        $expectedTemplateString = 'Template created: projects/' . $projectId . '/locations/' . self::$locationId . '/templates/' . self::$templateId;
        $this->assertStringContainsString($expectedTemplateString, $output);
    }

    // Helper functions

    /**
     * Creates DLP templates for inspect and deidentify configurations.
     *
     * @param string $projectId  Google Cloud Project ID
     * @param string $locationId Location ID
     *
     * @return array
     * @throws GaxApiException
     */
    public static function create_dlp_templates(string $projectId, string $locationId): array
    {
        // Specify regional endpoint.
        $options = ['apiEndpoint' => "dlp.$locationId.rep.googleapis.com"];

        // Instantiate a client.
        $dlpClient = new DlpServiceClient($options);

        // Generate unique template IDs.
        $inspectTemplateId = 'model-armor-inspect-template-' . uniqid();
        $deidentifyTemplateId = 'model-armor-deidentify-template-' . uniqid();
        $parent = $dlpClient->locationName($projectId, $locationId);

        try {
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
            $inspectTemplateResponse = $dlpClient->createInspectTemplate($inspectTemplateRequest);
            $inspectTemplateName = $inspectTemplateResponse->getName();

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
            $deidentifyTemplateResponse = $dlpClient->createDeidentifyTemplate($deidentifyTemplateRequest);
            $deidentifyTemplateName = $deidentifyTemplateResponse->getName();

            // Return template names.
            return [
                'inspectTemplateName' => $inspectTemplateName,
                'deidentifyTemplateName' => $deidentifyTemplateName,
            ];
        } catch (GaxApiException $e) {
            throw $e;
        }
    }

    /**
     * Deletes DLP templates for inspect and deidentify configurations.
     *
     * @param string $inspectTemplateName
     * @param string $deidentifyTemplateName
     * @param string $locationId
     *
     * @throws GaxApiException
     */
    public static function delete_dlp_templates(string $inspectTemplateName, string $deidentifyTemplateName, string $locationId): void
    {
        // Instantiate a client.
        $dlpClient = new DlpServiceClient([
            'apiEndpoint' => "dlp.{$locationId}.rep.googleapis.com",
        ]);

        try {
            // Delete inspect template.
            if ($inspectTemplateName) {
                $dlpDltInspectRequest = (new DeleteInspectTemplateRequest())->setName($inspectTemplateName);
                $dlpClient->deleteInspectTemplate($dlpDltInspectRequest);
            }

            // Delete deidentify template.
            if ($deidentifyTemplateName) {
                $dlpDltDeIndetifyRequest = (new DeleteDeidentifyTemplateRequest())->setName($deidentifyTemplateName);
                $dlpClient->deleteDeidentifyTemplate($dlpDltDeIndetifyRequest);
            }
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }
}
