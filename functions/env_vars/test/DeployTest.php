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

namespace Google\Cloud\Samples\Functions\ConceptsEnvVars\Test;

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

    private static $entryPoint = 'envVar';

    /**
     * Deploy the Function.
     *
     * Overrides CloudFunctionDeploymentTrait::doDeploy().
     */
    private static function doDeploy()
    {
        // Use the first test case as the source of the deployed environment variable.
        $cases = self::cases();
        $env = sprintf('%s=%s', $cases[0]['varName'], $cases[0]['varValue']);
        self::$fn->deploy(['--update-env-vars' => $env]);
    }

    /**
     * @dataProvider cases
     */
    public function testFunction(
        $statusCode,
        $varName,
        $varValue
    ): void {
        // Send a request to the function.
        $resp = $this->client->get('', [
            // Uncomment and CURLOPT_VERBOSE debug content will be sent to stdout.
            // 'debug' => true
        ]);

        // Assert status code.
        $this->assertEquals('200', $resp->getStatusCode());

        // Assert function output.
        $expected = 'bar';
        $actual = trim((string) $resp->getBody());
        // Failures often lead to a large HTML page in the response body.
        $this->assertEquals($expected, $actual);
    }

    protected static function deployFlags(array $flags = []): array
    {
        $flags['--update-env-vars'] = 'FOO=bar';
        return $flags;
    }
}
