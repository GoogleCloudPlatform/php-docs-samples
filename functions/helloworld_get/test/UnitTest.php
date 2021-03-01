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

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestCasesTrait.php';

/**
 * Unit tests for the Cloud Function.
 */
class UnitTest extends TestCase
{
    private static $entryPoint = 'helloGet';

    use TestCasesTrait;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../index.php';
    }

    /**
      * @dataProvider cases
      */
    public function testFunction($statusCode, $expected): void
    {
        $request = new ServerRequest('GET', '');
        $output = $this->runFunction(self::$entryPoint, [$request]);
        $this->assertStringContainsString($expected, $output);
    }

    private static function runFunction($functionName, array $params = []): string
    {
        return call_user_func_array($functionName, $params);
    }
}
