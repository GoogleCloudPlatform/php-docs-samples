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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class quickstartTest extends TestCase
{
    use TestTrait;

    public function testQuickstart()
    {
        $bucketName = sprintf(
            '%s-%s',
            $this->requireEnv('GOOGLE_STORAGE_BUCKET'),
            time()
        );
        $file = sys_get_temp_dir() . '/storage_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart.php');
        $contents = str_replace(
            ['YOUR_PROJECT_ID', 'my-new-bucket', '__DIR__'],
            [self::$projectId, $bucketName, sprintf('"%s/.."', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);

        // Invoke quickstart.php
        ob_start();
        $bucket = include $file;
        $output = ob_get_clean();

        // Make sure it looks correct
        $this->assertInstanceOf('Google\Cloud\Storage\Bucket', $bucket);
        $this->assertEquals($bucketName, $bucket->name());
        $bucket->delete();
    }
}
