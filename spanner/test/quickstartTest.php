<?php
/**
 * Copyright 2016 Google LLC
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

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\TestTrait;

class quickstartTest extends TestCase
{
    use TestTrait;

    protected static $tempFile;

    public function setUp()
    {
        $this->requireGrpc();
        $instanceId = $this->requireEnv('GOOGLE_SPANNER_INSTANCE_ID');
        $databaseId = $this->requireEnv('GOOGLE_SPANNER_DATABASE_ID');

        self::$tempFile = sys_get_temp_dir() . '/spanner_quickstart.php';
        $contents = str_replace(
            ['YOUR_PROJECT_ID', 'your-instance-id', 'your-database-id', '__DIR__'],
            [self::$projectId, $instanceId, $databaseId, sprintf('"%s/.."', __DIR__)],
            file_get_contents(__DIR__ . '/../quickstart.php')
        );
        file_put_contents(self::$tempFile, $contents);
    }

    public function testQuickstart()
    {
        // Invoke quickstart.php
        $output = $this->runSnippet(self::$tempFile);

        $this->assertContains('Hello World', $output);
    }
}
