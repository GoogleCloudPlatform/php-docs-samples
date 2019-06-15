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
use Symfony\Component\Console\Tester\CommandTester;

use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for transcribe commands.
 */
class authTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    private static $serviceAccountPath;

    public function setUpBeforeClass()
    {
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$serviceAccountPath = self::requireEnv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testAuthCloudImplicitCommand()
    {
        $output = $this->runCommand('auth-cloud-implicit', self::$projectId);
        $this->assertContains(self::$bucketName, $output);
    }

    public function testAuthCloudExplicitCommand()
    {
        $output = $this->runCommand('auth-cloud-explicit', self::$projectId, self::$serviceAccountPath);
        $this->assertContains(self::$bucketName, $output);
    }

    public function testAuthApiImplicitCommand()
    {
        $output = $this->runCommand('auth-api-implicit', self::$projectId);
        $this->assertContains(self::$bucketName, $output);
    }

    public function testAuthApiExplicitCommand()
    {
        $output = $this->runCommand('auth-api-explicit', self::$projectId, self::$serviceAccountPath);
        $this->assertContains(self::$bucketName, $output);
    }

    public function testAuthHttpImplicitCommand()
    {
        $output = $this->runCommand('auth-http-implicit', self::$projectId);
        $this->assertContains(self::$bucketName, $output);
    }

    public function testAuthHttpExplicitCommand()
    {
        $output = $this->runCommand('auth-http-explicit', self::$projectId, self::$serviceAccountPath);
        $this->assertContains(self::$bucketName, $output);
    }

    private function runCommand($commandName, $projectId = null, $serviceAccountPath=null)
    {
        $application = require __DIR__ . '/../auth.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        $args = array_filter([
            'projectId' => $projectId,
            'serviceAccountPath' => $serviceAccountPath,
        ]);

        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]
        );

        return ob_get_clean();
    }
}
