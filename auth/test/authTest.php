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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for transcribe commands.
 */
class authTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
        $this->bucketName = getenv('GOOGLE_STORAGE_BUCKET');
        $this->projectId = getenv('GCLOUD_PROJECT');
    }

    public function testAuthCloudImplicitCommand()
    {
        $output = $this->runCommand('auth-cloud-implicit', $this->projectId);
        $this->assertContains($this->bucketName, $output);
    }

    public function testAuthCloudExplicitCommand()
    {
        $serviceAccountPath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        $output = $this->runCommand('auth-cloud-explicit', $this->projectId, $serviceAccountPath);
        $this->assertContains($this->bucketName, $output);
    }

    public function testAuthCloudExplicitComputeEngineCommand()
    {
        $output = $this->runCommand(
            'auth-cloud-explicit-compute-engine', $this->projectId);
        $this->assertContains('Undefined index: access_token', $output);
    }

    public function testAuthCloudExplicitAppEngineCommand()
    {
        $output = $this->runCommand(
            'auth-cloud-explicit-app-engine', $this->projectId);
        $this->assertContains('Undefined index: access_token', $output);
    }

    public function testAuthApiImplicitCommand()
    {
        $output = $this->runCommand('auth-api-implicit', $this->projectId);
        $this->assertContains($this->bucketName, $output);
    }

    public function testAuthApiExplicitCommand()
    {
        $serviceAccountPath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        $output = $this->runCommand('auth-api-explicit', $this->projectId, $serviceAccountPath);
        $this->assertContains($this->bucketName, $output);
    }

    public function testAuthApiExplicitComputeEngineCommand()
    {
        $output = $this->runCommand(
            'auth-api-explicit-compute-engine', $this->projectId);
        $this->assertContains('Invalid Credentials', $output);
    }

    public function testAuthApiExplicitAppEngineCommand()
    {
        $output = $this->runCommand(
            'auth-api-explicit-app-engine', $this->projectId);
        $this->assertContains('Invalid Credentials', $output);
    }

    private function runCommand($commandName, $projectId = null, $serviceAccountPath=null)
    {
        $application = require __DIR__ . '/../auth.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        if ($serviceAccountPath) {
            $args = array_filter([
                'projectId' => $projectId,
                'serviceAccountPath' => $serviceAccountPath,
            ]);
        } else {
            $args = array_filter([
                'projectId' => $projectId,
            ]);
        }

        ob_start();
        try {
            $commandTester->execute(
                $args,
                ['interactive' => false]
            );
        } catch (\Google\Cloud\Core\Exception\ServiceException $e) {
            ob_get_clean();
            $application->renderException($e, $commandTester->getOutput());
            return $commandTester->getDisplay();
        } catch (\Google_Service_exception $e) {
            ob_get_clean();
            $application->renderException($e, $commandTester->getOutput());
            return $commandTester->getDisplay();
        }
        return ob_get_clean();
    }
}
