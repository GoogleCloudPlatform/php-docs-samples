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

namespace Google\Cloud\Samples\Functions\HelloworldGet\Test;

use Google\Cloud\TestUtils\CloudFunctionDeploymentTrait;
use PHPUnit\Framework\TestCase;

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

    private static $entryPoint = 'scopeDemo';

    public function testFunction(): void
    {
        // Send a request to the function.
        $firstResp = $this->client->post('', [
            // Uncomment and CURLOPT_VERBOSE debug content will be sent to stdout.
            // 'debug' => true
        ]);

        sleep(1); // avoid race condition

        $secondResp = $this->client->post('', [
            // Uncomment and CURLOPT_VERBOSE debug content will be sent to stdout.
            // 'debug' => true
        ]);

        // Assert status codes.
        $this->assertEquals('200', $firstResp->getStatusCode());
        $this->assertEquals('200', $secondResp->getStatusCode());

        $firstOutput = trim((string) $firstResp->getBody());
        $secondOutput = trim((string) $secondResp->getBody());

        // Assert generic function output.
        $this->assertStringContainsString('Per instance: 120', $firstOutput);
        $this->assertStringContainsString('Per function: 15', $firstOutput);

        // Assert caching behavior.
        $this->assertStringContainsString('Cache empty', $firstOutput);
        $this->assertStringContainsString('Reading cached value', $secondOutput);
    }
}
