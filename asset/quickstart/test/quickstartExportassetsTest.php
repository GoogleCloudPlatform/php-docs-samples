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

use Google\Cloud\Storage\StorageClient;
use PHPUnit\Framework\TestCase;

class quickstartTest extends TestCase
{
    public function testQuickstartExportassets()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }

        $bucketName = 'assets-bucket-' . time();
        $fileName = 'my-assets.txt';
        $assetFilePath = 'gs://' . $bucketName . '/' . $fileName;
        $fileToBeTested = sys_get_temp_dir() . '/quickstart_exportassets.php';
        $contents = file_get_contents(__DIR__ . '/../quickstart_exportassets.php');
        $contents = str_replace(
            ['YOUR_PROJECT_ID', 'YOUR_ASSETS_FILE', '__DIR__'],
            [$projectId, $assetFilePath, sprintf('"%s/.."', __DIR__)],
            $contents
          );
        file_put_contents($fileToBeTested, $contents);

        $storage = new StorageClient(['projectId' => $projectId]);
        $bucket = $storage->createBucket($bucketName);
        // Make sure it looks correct
        $this->assertInstanceOf('Google\Cloud\Storage\Bucket', $bucket);
        $this->assertEquals($bucketName, $bucket->name());

        // Invoke quickstart.php
        ob_start();
        include $fileToBeTested;
        $output = ob_get_clean();

        $assetFile = $bucket->object($fileName);
        $this->assertEquals($assetFile->name(), $fileName);
        $assetFile->delete();
        $bucket->delete();
    }
}
