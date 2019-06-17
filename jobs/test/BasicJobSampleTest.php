<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\Jobs;

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class BasicJobSampleTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../jobs.php';

    public function testBasicJobSample()
    {
        $output = $this->runCommand('basic-job');
        $this->assertRegExp('/Job generated:.*Job created:.*Job existed:.*Job updated:'
            . '.*changedDescription.*Job updated:.*changedJobTitle.*Job deleted/s', $output);
    }
}
