<?php
/*
 * Copyright 2020 Google LLC.
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

use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class quickstartTest extends TestCase
{
    use TestTrait;

    private static $secretId;

    public static function setUpBeforeClass()
    {
        self::$secretId = uniqid('php-quickstart-');
    }

    public static function tearDownAfterClass()
    {
        $client = new SecretManagerServiceClient();
        $name = $client->secretName(self::$projectId, self::$secretId);

        try {
            $client->deleteSecret($name);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    public function testQuickstart()
    {
        $output = self::runSnippet(__DIR__ . '/../quickstart.php', [
            self::$projectId,
            self::$secretId,
        ]);
        $this->assertContains('Plaintext: hello world', $output);
    }
}
