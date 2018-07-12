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


use Symfony\Component\Console\Tester\CommandTester;

class CommuteSearchSampleTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;

    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return $this->markTestSkipped("Set the GOOGLE_APPLICATION_CREDENTIALS environment variable.");
        }

        $application = require __DIR__ . '/../cjd_sample.php';
        $this->commandTester = new CommandTester($application->get('commute-search'));
    }

    public function testFeaturedJobsSearchSample()
    {
        $this->commandTester->execute([], ['interactive' => false]);
        $this->expectOutputRegex('/.*appliedCommuteFilter.*1600 Amphitheatre Pkwy.*/s');
    }
}
