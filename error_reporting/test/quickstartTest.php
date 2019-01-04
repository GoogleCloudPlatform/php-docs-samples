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

class quickstartTest extends TestCase
{
    use VerifyReportedErrorTrait;

    public function testQuickstart()
    {
        $version = 'quickstart-tests-' . time();
        $file = sys_get_temp_dir() . '/error_reporting_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart.php');
        $contents = str_replace(
            ['YOUR_PROJECT_ID', "'test'", '__DIR__'],
            [
                self::$projectId,
                var_export($version, true),
                sprintf('"%s/.."', __DIR__)
            ],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart.php
        ob_start();
        passthru(sprintf('php %s', $file));
        $output = ob_get_clean();

        // Make sure it worked
        $this->assertContains('Throwing a test exception', $output);
        $this->verifyReportedError(self::$projectId, 'quickstart.php test exception');
    }
}
