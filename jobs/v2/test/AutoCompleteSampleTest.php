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

class AutoCompleteSampleTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;

    public function setUp()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return $this->markTestSkipped("Set the GOOGLE_APPLICATION_CREDENTIALS environment variable.");
        }

        $application = require __DIR__ . '/../jobs.php';
        $this->commandTester = new CommandTester($application->get('auto-complete'));
    }

    public function testAutoCompleteSample()
    {
        $this->commandTester->execute([], ['interactive' => false]);
        $this->expectOutputRegex('/completionResults.*"suggestion"\s*:\s*"Google",\s+"type"\s*:\s*"COMPANY_NAME"/s');
        $this->assertEquals(2,
            preg_match_all('/"suggestion"\s*:\s*"Software Engineer",\s+"type"\s*:\s*"JOB_TITLE"/s',
                $this->getActualOutput()),
            2);
    }
}
