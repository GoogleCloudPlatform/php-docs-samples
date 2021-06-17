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

namespace Google\Cloud\Samples\Functions\HttpMethod\Test;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Unit tests for the Cloud Function.
 */
class UnitTest extends TestCase
{
    use TestCasesTrait;

    private static $entryPoint = 'httpMethod';

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
        $content
    ): void {
        $request = new ServerRequest($method, '/');
        $output = $this->runFunction(self::$entryPoint, [$request]);
        $this->assertEquals(
            $statusCode,
            $output->getStatusCode()
        );
        $this->assertStringContainsString(
            $content,
            (string) $output->getBody()
        );
    }

    private static function runFunction($functionName, array $params = []): Response
    {
        return call_user_func_array($functionName, $params);
    }
}
