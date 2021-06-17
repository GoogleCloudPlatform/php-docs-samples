<?php
/**
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

namespace Google\Cloud\Samples\Functions\SlackSlashCommand\Test;

use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class DeployTest.
 *
 * This test is not run by the CI system.
 *
 * To skip deployment of a new function, run with "GOOGLE_SKIP_DEPLOYMENT=true".
 * To skip deletion of the tested function, run with "GOOGLE_KEEP_DEPLOYMENT=true".
 * @group deploy
 */
class DeployTest extends TestCase
{
    use CloudFunctionDeploymentTrait;
    use TestCasesTrait;

    /**
     * @dataProvider cases
     */
    public function testFunction(
        $label,
        $body,
        $method,
        $expected,
        $statusCode,
        $headers
    ): void {
        $response = $this->client->request(
            $method,
            '',
            ['headers' => $headers, 'body' => $body]
        );
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $label . ': status code'
        );

        if ($expected !== null) {
            $output = (string) $response->getBody();
            $this->assertStringContainsString($expected, $output, $label . ': contains');
        }
    }

    /**
     * Deploy the Function.
     *
     * Overrides CloudFunctionLocalTestTrait::doDeploy().
     */
    private static function doDeploy()
    {
        // Forward required env variables to Cloud Functions.
        $envVars = sprintf(
            'SLACK_SECRET=%s,KG_API_KEY=%s',
            self::$slackSecret,
            self::$kgApiKey
        );

        self::$fn->deploy(['--update-env-vars' => $envVars]);
    }
}
