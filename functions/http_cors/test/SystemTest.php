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

    /**
      * @dataProvider cases
      */
    public function testFunction(
        $url,
        $method,
        $status_code,
        $contains_header,
        $not_contains_header,
        $contains_content,
        $not_contains_content
    ): void {
        // Send a request to the function.
        $resp = $this->client->request($method, $url);

        // Assert status code.
        $this->assertEquals($status_code, $resp->getStatusCode());

        // Assert headers.
        $header_names = array_keys($resp->getHeaders());
        if ($contains_header) {
            $this->assertContains(
                $contains_header,
                $header_names
            );
        }
        if ($not_contains_header) {
            $this->assertNotContains(
                $not_contains_header,
                $header_names
            );
        }

        // Assert function output.
        $content = trim((string) $resp->getBody());
        if ($contains_content) {
            $this->assertContains(
                $contains_content,
                $content
            );
        }
        if ($not_contains_content) {
            $this->assertNotContains(
                $not_contains_content,
                $content
            );
        }
    }
}
