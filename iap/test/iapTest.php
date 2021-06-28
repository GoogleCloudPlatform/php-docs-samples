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
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IAP commands.
 */
class iapTest extends TestCase
{
    use TestTrait;

    public function testRequestAndValidate()
    {
        // Make a request to our IAP URL, which returns the IAP's JWT Assertion.
        $output = $this->runFunctionSnippet('make_iap_request', [
            'url' => $this->requireEnv('IAP_URL'),
            'clientId' => $this->requireEnv('IAP_CLIENT_ID')
        ]);

        // Verify an ID token was returned
        $this->assertStringContainsString('Printing out response body:', $output);
        list($_, $iapJwt) = explode(':', $output);

        $projectNumber = $this->requireEnv('IAP_PROJECT_NUMBER');
        $projectId = $this->requireEnv('IAP_PROJECT_ID');

        // Now validate the JWT using the validation command
        $output = $this->runFunctionSnippet('validate_jwt', [
            $iapJwt,
            sprintf('/projects/%s/apps/%s', $projectNumber, $projectId),
        ]);
        $this->assertStringContainsString('Printing user identity information from ID token payload:', $output);
        $this->assertStringContainsString('sub: accounts.google.com', $output);
        $this->assertStringContainsString('email:', $output);
    }

    public function testInvalidJwt()
    {
        $output = $this->runFunctionSnippet('validate_jwt', [
            'fake_j.w.t',
            'fake_expected_audience'
        ]);
        $this->assertStringContainsString('Failed to validate JWT:', $output);
    }
}
