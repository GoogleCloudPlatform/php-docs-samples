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
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class quickstartTest extends TestCase
{
    use TestTrait;

    private static $parameterId;
    private static $locationId;
    private static $versionId;

    public static function setUpBeforeClass(): void
    {
        self::$parameterId = uniqid('php-quickstart-');
        self::$versionId = uniqid('php-quickstart-');
        self::$locationId = 'global';
    }

    public static function tearDownAfterClass(): void
    {
        $client = new ParameterManagerClient();
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
    }

    public function testQuickstart()
    {
        $output = self::runSnippet('quickstart', [
            self::$projectId,
            self::$parameterId,
            self::$versionId,
        ]);

        $this->assertStringContainsString('Created parameter', $output);
        $this->assertStringContainsString('Created parameter version', $output);
        $this->assertStringContainsString('Payload', $output);
    }
}
