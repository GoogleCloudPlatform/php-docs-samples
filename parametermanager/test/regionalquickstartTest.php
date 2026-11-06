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

use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\DeleteParameterRequest;
use Google\Cloud\ParameterManager\V1\DeleteParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\DeleteTemplateRequest;
use Google\Cloud\ParameterManager\V1\DeleteTemplateVersionRequest;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class regionalquickstartTest extends TestCase
{
    use TestTrait;

    private static $parameterId;
    private static $locationId;
    private static $versionId;
    private static $templateId;

    public static function setUpBeforeClass(): void
    {
        self::$parameterId = uniqid('php-quickstart-');
        self::$versionId = uniqid('php-quickstart-');
        self::$templateId = uniqid('php-quickstart-');
        self::$locationId = 'us-central1';
    }

    public static function tearDownAfterClass(): void
    {
        $options = ['apiEndpoint' => 'parametermanager.' . self::$locationId . '.rep.googleapis.com'];
        $client = new ParameterManagerClient($options);
        $parameterName = $client->parameterName(self::$projectId, self::$locationId, self::$parameterId);
        $parameterVersionName = $client->parameterVersionName(self::$projectId, self::$locationId, self::$parameterId, self::$versionId);

        try {
            $deleteVersionRequest = (new DeleteParameterVersionRequest())
                ->setName($parameterVersionName);
            $client->deleteParameterVersion($deleteVersionRequest);

            $deleteParameterRequest = (new DeleteParameterRequest())
                ->setName($parameterName);
            $client->deleteParameter($deleteParameterRequest);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }

        $templateName = $client->templateName(self::$projectId, self::$locationId, self::$templateId);
        $templateVersionName = $client->templateVersionName(self::$projectId, self::$locationId, self::$templateId, self::$versionId);

        try {
            $client->deleteTemplateVersion((new DeleteTemplateVersionRequest())->setName($templateVersionName));
            $client->deleteTemplate((new DeleteTemplateRequest())->setName($templateName));
            $client->deleteParameterVersion((new DeleteParameterVersionRequest())->setName($client->parameterVersionName(self::$projectId, self::$locationId, self::$parameterId . '-template', self::$versionId)));
            $client->deleteParameter((new DeleteParameterRequest())->setName($client->parameterName(self::$projectId, self::$locationId, self::$parameterId . '-template')));
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    public function testQuickstart()
    {
        $output = self::runSnippet('regional_quickstart', [
            self::$projectId,
            self::$locationId,
            self::$parameterId,
            self::$versionId,
        ]);

        $this->assertStringContainsString('Created regional parameter', $output);
        $this->assertStringContainsString('Created regional parameter version', $output);
        $this->assertStringContainsString('Payload', $output);
    }

    public function testTemplateQuickstart()
    {
        $output = self::runSnippet('regional_template_quickstart', [
            self::$projectId,
            self::$locationId,
            self::$templateId,
            self::$parameterId . '-template',
            self::$versionId,
        ]);

        $this->assertStringContainsString('Created regional template', $output);
        $this->assertStringContainsString('Created regional template version', $output);
        $this->assertStringContainsString('Created regional parameter', $output);
        $this->assertStringContainsString('Created regional parameter version', $output);
        $this->assertStringContainsString('Rendered payload: {"username":"test-user","host":"localhost"}', $output);
    }
}
