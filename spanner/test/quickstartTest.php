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

namespace Google\Cloud\Samples\Spanner;

class quickstartTest extends \PHPUnit_Framework_TestCase
{
    public function testQuickstart()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }
        if (!$instanceId = getenv('GOOGLE_SPANNER_INSTANCE_ID')) {
            $this->markTestSkipped('GOOGLE_SPANNER_INSTANCE_ID must be set.');
        }
        if (!$databaseId = getenv('GOOGLE_SPANNER_DATABASE_ID')) {
            $this->markTestSkipped('GOOGLE_SPANNER_DATABASE_ID must be set.');
        }

        $file = sys_get_temp_dir() . '/spanner_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart.php');
        $contents = str_replace(
            ['YOUR_PROJECT_ID', 'my-instance-id', 'my-database-id', '__DIR__'],
            [$projectId, $instanceId, $databaseId, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart.php
        $results = include $file;

        $this->expectOutputString('Hello World' . PHP_EOL);
    }
}
