<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\ErrorReporting;

use PHPUnit\Framework\TestCase;

// Load the testing trait
require_once __DIR__ . '/VerifyReportedErrorTrait.php';

class report_errorTest extends TestCase
{
    use VerifyReportedErrorTrait;

    public function testReportError()
    {
        $message = sprintf('Test Report Error (%s)', date('Y-m-d H:i:s'));
        $output = $this->runSnippet('report_error', [
            $message,
            'unittests@google.com',
        ]);
        $this->assertContains(
            'Reported an exception to Stackdriver' . PHP_EOL,
            $output
        );

        $this->verifyReportedError(self::$projectId, $message);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId], $params);
        ob_start();
        require __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }
}
