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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for IAP commands.
 */
class iapTest extends \PHPUnit_Framework_TestCase
{
    private $url;
    private $clientId;
    private $serviceAccountPath;

    public function setUp()
    {
        if (!$this->url = getenv('IAP_URL')) {
            $this->markTestSkipped('No IAP protected resource URL found.');
        } elseif (!$this->clientId = getenv('IAP_CLIENT_ID')) {
            $this->markTestSkipped('No OAuth client ID found.');
        } elseif (!$this->serviceAccountPath = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('No IAP service account found.');
        }
    }

    public function testRequest()
    {
        $output = $this->runCommand('request');
        $this->assertContains('x-goog-authenticated-user-jwt:', $output);
    }

    public function testValidate()
    {
        if (version_compare(PHP_VERSION, '7.2.0') === 1) {
            $this->markTestSkipped('Validate is not yet supported on PHP 7.2');
        }
        if (!$projectNumber = getenv('IAP_PROJECT_NUMBER')) {
            $this->markTestSkipped('No IAP project number found.');
        } elseif (!$projectId = getenv('IAP_PROJECT_ID')) {
            $this->markTestSkipped('No IAP project ID found.');
        }
        $output = $this->runCommand('validate', [
            'projectNumber' => $projectNumber,
            'projectId' => $projectId
        ]);
        $this->assertContains('Printing user identity information from ID token payload:', $output);
        $this->assertContains('sub: accounts.google.com', $output);
        $this->assertContains('email:', $output);
    }

    private function runCommand($name, $options = [])
    {
        $application = require __DIR__ . '/../iap.php';
        $command = $application->get($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url' => $this->url,
            'clientId' => $this->clientId,
            'serviceAccountPath' => $this->serviceAccountPath
        ] + $options, [
            'interactive' => false
        ]);
        return $commandTester->getDisplay();
    }
}
