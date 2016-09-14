<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Speech\Tests;

/**
 * Unit Tests for misc functions.
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.raw';

        $base64Audio = require __DIR__ . '/../src/functions/base64_encode_audio.php';

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(base64_decode($base64Audio), stream_get_contents($audioFileResource));
    }
}
