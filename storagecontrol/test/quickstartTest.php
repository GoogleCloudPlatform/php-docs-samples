<?php
/**
 * Copyright 2024 Google Inc.
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

use Google\Cloud\Storage\Control\V2\StorageLayout;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class quickstartTest extends TestCase
{
    use TestTrait;
    private $bucket;
    private $bucketName;
    private $storageClient;

    public function setUp(): void
    {
        $this->bucketName = sprintf(
            '%s-%s',
            $this->requireEnv('GOOGLE_STORAGE_BUCKET'),
            time()
        );
        $this->storageClient = new StorageClient();
        $this->bucket = $this->storageClient->createBucket($this->bucketName);
    }

    public function tearDown(): void
    {
        $this->bucket->delete();
    }

    public function testQuickstart()
    {
        $file = $this->prepareFile();
        // Invoke quickstart.php
        ob_start();
        $response = include $file;
        $output = ob_get_clean();

        // Make sure it looks correct
        $this->assertInstanceOf(StorageLayout::class, $response);
        $this->assertEquals(
            sprintf(
                'Performed get_storage_layout request for projects/_/buckets/%s/storageLayout' . PHP_EOL,
                $this->bucketName
            ),
            $output
        );
    }

    private function prepareFile()
    {
        $file = sys_get_temp_dir() . '/storage_control_quickstart.php';
        $contents = file_get_contents(__DIR__ . '/../src/quickstart.php');
        $contents = str_replace(
            ['my-new-bucket', '__DIR__'],
            [$this->bucketName, sprintf('"%s"', __DIR__)],
            $contents
        );
        file_put_contents($file, $contents);
        return $file;
    }
}
