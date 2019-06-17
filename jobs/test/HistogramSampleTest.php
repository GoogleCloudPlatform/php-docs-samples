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

use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class HistogramSampleTest extends TestCase
{
    use TestTrait;
    private $commandTester;

    public function setUp()
    {
        $application = require __DIR__ . '/../jobs.php';
        $this->commandTester = new CommandTester($application->get('histogram'));
    }

    public function testHistogramSample()
    {
        $this->commandTester->execute([], ['interactive' => false]);
        $this->expectOutputRegex('/COMPANY_ID/');
        $this->expectOutputRegex('/someFieldName1/');
    }
}
