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

namespace Google\Cloud\Samples\Functions\Helloworld\Test;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Cloud Function.
 */
class FunctionTest extends TestCase
{
    private static $name = 'helloGet';

    public function setUp()
    {
        require __DIR__ . '/../index.php';
    }

    public function testHelloHttp()
    {
        $request = new ServerRequest('GET', '/');
        $output = $this->runFunction(self::$name, [$request]);
        $this->assertContains('Hello, World!', $output);
    }

    private static function runFunction($functionName, array $params = [])
    {
        return call_user_func_array($functionName, $params);
    }
}
