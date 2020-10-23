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

namespace Google\Cloud\Samples\Functions\HttpCors\Test;

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
 */
class DeployTest extends TestCase
{
    use CloudFunctionDeploymentTrait;
    use TestCasesTrait;

    private static $name = 'corsEnabledFunction';

    public function testFunction(): void
    {
        foreach (self::cases() as $test) {
            // Send a request to the function.
            // ($test['url'] is absolute, so don't use it here)
            $response = $this->client->request($test['method'], '', [
                // Uncomment and CURLOPT_VERBOSE debug content will be sent to stdout.
                // 'debug' => true
            ]);

            // Assert status code.
            $this->assertEquals(
                $response->getStatusCode(),
                $test['status_code']
            );
            
            // Assert headers.
            $header_names = array_keys($response->getHeaders());
            if (isset($test['contains_header'])) {
                $this->assertContains(
                    $test['contains_header'],
                    $header_names
                );
            }
            if (isset($test['not_contains_header'])) {
                $this->assertNotContains(
                    $test['not_contains_header'],
                    $header_names
                );
            }

            // Assert content.
            $content = (string) $response->getBody();
            if (isset($test['contains_content'])) {
                $this->assertContains(
                    $test['contains_content'],
                    $content
                );
            }
            if (isset($test['not_contains_content'])) {
                $this->assertNotContains(
                    $test['not_contains_content'],
                    $content
                );
            }
        }
    }
}
