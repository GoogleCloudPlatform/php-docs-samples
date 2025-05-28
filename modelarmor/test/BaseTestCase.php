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

abstract class BaseTestCase extends TestCase
{
    use TestTrait;

    protected static $client;
    protected static $templateId;
    protected static $locationId = 'us-central1';

    public static function setUpBeforeClass(): void
    {
        $options = ['apiEndpoint' => 'modelarmor.' . self::$locationId . '.rep.googleapis.com'];
        self::$client = new ModelArmorClient($options);
        self::$templateId = static::getTemplatePrefix() . uniqid();
    }

    public static function tearDownAfterClass(): void
    {
        $templateName = self::$client->templateName(self::$projectId, self::$locationId, self::$templateId);
        try {
            static::customTeardown();
            $request = (new DeleteTemplateRequest())->setName($templateName);
            self::$client->deleteTemplate($request);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
        self::$client->close();
    }

    abstract protected static function getTemplatePrefix(): string;
    protected static function customTeardown(): void {}

    protected function runSnippetfile(string $snippetName, array $params = []): string
    {
        $output = $this->runSnippet($snippetName, $params);
        return $output;
    }

    protected static function getProjectId()
    {
        return self::$projectId;
    }
}
