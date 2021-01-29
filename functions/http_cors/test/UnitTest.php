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
    private static $entryPoint = 'corsEnabledFunction';

    use TestCasesTrait;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../index.php';
    }

    /**
      * @dataProvider cases
      */
    public function testFunction(
        $method,
        $statusCode,
        $containsHeader,
        $notContainsHeader,
        $containsContent,
        $notContainsContent
    ): void {
        $request = new ServerRequest($method, '/');
        $response = $this->runFunction(self::$entryPoint, [$request]);

        // Assert status code.
        $this->assertEquals(
            $response->getStatusCode(),
            $statusCode
        );

        // Assert headers.
        $header_names = array_keys($response->getHeaders());
        if ($containsHeader) {
            $this->assertContains(
                $containsHeader,
                $header_names
            );
        }
        if ($notContainsHeader) {
            $this->assertNotContains(
                $notContainsHeader,
                $header_names
            );
        }

        // Assert content.
        $content = (string) $response->getBody();
        if ($containsContent) {
            $this->assertStringContainsString(
                $containsContent,
                $content
            );
        }
        if ($notContainsContent) {
            $this->assertStringNotContainsString(
                $notContainsContent,
                $content
            );
        }
    }

    private static function runFunction($functionName, array $params = []): Response
    {
        return call_user_func_array($functionName, $params);
    }
}
