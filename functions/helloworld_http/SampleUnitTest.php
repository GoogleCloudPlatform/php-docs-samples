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

// [START functions_http_unit_test]

namespace Google\Cloud\Samples\Functions\HelloworldHttp;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class SampleUnitTest.
 *
 * Unit test for helloHttp.
 */
class SampleUnitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/index.php';
    }

    public function testFunction(): void
    {
        $name = uniqid();
        $request = new ServerRequest('POST', '/', [], json_encode(['name' => $name]));
        $expected = sprintf('Hello, %s!', $name);
        $actual = helloHttp($request);
        $this->assertEquals($expected, $actual);
    }
}

// [END functions_http_unit_test]
