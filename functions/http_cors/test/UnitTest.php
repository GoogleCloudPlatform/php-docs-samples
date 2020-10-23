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

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Unit tests for the Cloud Function.
 */
class UnitTest extends TestCase
{
    private static $name = 'corsEnabledFunction';

    use TestCasesTrait;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../index.php';
    }

    public function testFunction(): void
    {
        foreach (self::cases() as $test) {
            $request = new ServerRequest($test['method'], $test['url']);
            $response = $this->runFunction(self::$name, [$request]);

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

    private static function runFunction($functionName, array $params = []): Response
    {
        return call_user_func_array($functionName, $params);
    }
}
