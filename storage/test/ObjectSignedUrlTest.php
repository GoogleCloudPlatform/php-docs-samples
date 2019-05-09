<?php
/**
 * Copyright 2019 Google LLC.
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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\Storage\StorageClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for RequesterPaysCommand.
 */
class ObjectSignedUrlTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $bucketName;
    private static $objectName;
    private static $commandFile = __DIR__ . '/../storage.php';

    /** @beforeClass */
    public static function setUpObject()
    {
        $storage = new StorageClient();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$objectName = sprintf('test-object-%s', time());
        $storage
            ->bucket(self::$bucketName)
            ->upload("test file content", [
                'name' => self::$objectName
            ]);
    }

    public function testGetV2SignedUrl()
    {
        $output = $this->runCommand('get-object-v2-signed-url', [
            'bucket' => self::$bucketName,
            'object' => self::$objectName,
        ]);

        $this->assertContains("The signed url for " . self::$objectName . " is", $output);
    }
}
