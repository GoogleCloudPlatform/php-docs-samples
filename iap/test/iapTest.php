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
namespace Google\Cloud\Samples\Iap\Tests;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
/**
 * Unit Tests for IAP commands.
 */
class iapTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    public static function setUpBeforeClass()
    {
    }

    public function setUp()
    {
    }

    public function testRequest()
    {
        if (!$url = getenv('IAP_URL')) {
            $this->markTestSkipped('No IAP protected resource URL found.');
        } else if (!$clientId = getenv('IAP_CLIENT_ID')) {
            $this->markTestSkipped('No OAuth client ID found.');
        } else if (!$serviceAccountPath = getenv('IAP_SERVICE_ACCOUNT')) {
            $this->markTestSkipped('No IAP service account found.');
        }
        $output = $this->runRequestCommand($url, $clientId, $serviceAccountPath);
        $this->assertContains('x-goog-authenticated-user-jwt:', $output);
    }

    public function testValidate()
    {
        if (!$url = getenv('IAP_URL')) {
            $this->markTestSkipped('No IAP protected resource URL found.');
        } else if (!$clientId = getenv('IAP_CLIENT_ID')) {
            $this->markTestSkipped('No OAuth client ID found.');
        } else if (!$serviceAccountPath = getenv('IAP_SERVICE_ACCOUNT')) {
            $this->markTestSkipped('No IAP service account found.');
        } else if (!$projectNumber = getenv('IAP_PROJECT_NUMBER')) {
            $this->markTestSkipped('No IAP project number found.');
        } else if (!$projectId = getenv('IAP_PROJECT_ID')) {
            $this->markTestSkipped('No IAP project ID found.');
        }
        $output = $this->runValidateCommand($url, $clientId, $serviceAccountPath, $projectNumber, $projectId);
        $this->assertContains('Printing out user identity information from ID token payload:', $output);
        $this->assertContains('sub: accounts.google.com', $output);
        $this->assertContains('email:', $output);
        $this->assertContains($projectId, $output);
    }

    private function runRequestCommand($url, $clientId, $serviceAccountPath)
    {
        $application = require __DIR__ . '/../iap.php';
        $command = $application->get('request');
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute([
            'url' => $url,
            'clientId' => $clientId,
            'serviceAccountPath' => $serviceAccountPath,
        ], [
            'interactive' => false
        ]);
        return ob_get_clean();
    }

    private function runValidateCommand($url, $clientId, $serviceAccountPath, $projectNumber, $projectId)
    {
        $application = require __DIR__ . '/../iap.php';
        $command = $application->get('validate');
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute([
            'url' => $url,
            'clientId' => $clientId,
            'serviceAccountPath' => $serviceAccountPath,
            'projectNumber' => $projectNumber,
            'projectId' => $projectId,
        ], [
            'interactive' => false
        ]);
        return ob_get_clean();
    }
}