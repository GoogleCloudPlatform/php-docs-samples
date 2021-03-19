<?php
/**
 * Copyright 2021 Google Inc.
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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class quickstartJsonCredentialsTest extends TestCase
{
    use TestTrait;

    public function testQuickstart()
    {
        $file = sys_get_temp_dir() . '/analyticsdata_json_credentials_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart_json_credentials.php');
        $test_property_id = self::$GA_TEST_PROPERTY_ID || '222596558';
        $contents = str_replace(
            ['YOUR-GA4-PROPERTY-ID', '__DIR__'],
            [$test_property_id, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart_json_credentials.php
        $output = $this->runSnippet($file);

        $this->assertRegExp('/Report result/', $output);
    }
}
