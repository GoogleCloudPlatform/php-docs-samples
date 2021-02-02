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
namespace Google\Cloud\Samples\Iap;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IAP commands.
 */
class iapTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../iap.php';

    public function testRequestAndValidate()
    {
        // Make a request to our IAP URL, which returns the IAP's JWT Assertion.
        $output = $this->runCommand('request', [
            'url' => $this->requireEnv('IAP_URL'),
            'clientId' => $this->requireEnv('IAP_CLIENT_ID'),
            'serviceAccountPath' => $this->requireEnv('GOOGLE_APPLICATION_CREDENTIALS'),
        ]);

        // Verify an ID token was returned
        $this->assertStringContainsString('Printing out response body:', $output);
        list($_, $iapJwt) = explode(':', $output);

        // Now validate the JWT using the validation command
        $output = $this->runCommand('validate', [
            'jwt' => $iapJwt,
            'projectNumber' => $this->requireEnv('IAP_PROJECT_NUMBER'),
            'projectId' => $this->requireEnv('IAP_PROJECT_ID'),
        ]);
        $this->assertStringContainsString('Printing user identity information from ID token payload:', $output);
        $this->assertStringContainsString('sub: accounts.google.com', $output);
        $this->assertStringContainsString('email:', $output);
    }

    public function testInvalidJwt()
    {
        validate_jwt('fake_j.w.t', 'fake_expected_audience');
        $this->expectOutputRegex('/Failed to validate JWT:/');
    }
}
