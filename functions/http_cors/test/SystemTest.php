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

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Class SystemTest.
 */
class SystemTest extends TestCase
{
    use CloudFunctionLocalTestTrait;
    use TestCasesTrait;

    private static $name = 'corsEnabledFunction';

    public function testFunction(): void
    {
        foreach (self::cases() as $test) {
            // Send a request to the function.
            $resp = $this->client->request(
                $test['method'],
                $test['url']
            );

            // Assert status code.
            $this->assertEquals($test['status_code'], $resp->getStatusCode());

            // Assert headers.
            $header_names = array_keys($resp->getHeaders());
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

            // Assert function output.
            $content = trim((string) $resp->getBody());
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
