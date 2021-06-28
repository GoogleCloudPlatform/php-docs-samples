<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Samples\Auth;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for transcribe commands.
 */
class authTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    private static $serviceAccountPath;

    public static function setUpBeforeClass(): void
    {
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$serviceAccountPath = self::requireEnv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testAuthCloudImplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_cloud_implicit', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }

    public function testAuthCloudExplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_cloud_explicit', [
            'projectId' => self::$projectId,
            'serviceAccountPath' => self::$serviceAccountPath,
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }

    public function testAuthApiImplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_api_implicit', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }

    public function testAuthApiExplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_api_explicit', [
            'projectId' => self::$projectId,
            'serviceAccountPath' => self::$serviceAccountPath,
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }

    public function testAuthHttpImplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_http_implicit', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }

    public function testAuthHttpExplicitCommand()
    {
        $output = $this->runFunctionSnippet('auth_http_explicit', [
            'projectId' => self::$projectId,
            'serviceAccountPath' => self::$serviceAccountPath
        ]);
        $this->assertStringContainsString(self::$bucketName, $output);
    }
}
