<?php

/**
 * Copyright 2022 Google LLC.
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
declare(strict_types=1);

namespace Google\Cloud\Samples\Media\LiveStream;

use Google\ApiCore\ApiException;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Live Stream API commands.
 */
class livestreamTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $projectId;
    private static $location = 'us-central1';
    private static $inputIdPrefix = 'php-test-input';
    private static $inputId;
    private static $inputName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldInputs();
    }

    public function testCreateInput()
    {
        self::$inputId = sprintf('%s-%s-%s', self::$inputIdPrefix, uniqid(), time());
        self::$inputName = sprintf('projects/%s/locations/%s/inputs/%s', self::$projectId, self::$location, self::$inputId);

        $output = $this->runFunctionSnippet('create_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testCreateInput */
    public function testListInputs()
    {
        $output = $this->runFunctionSnippet('list_inputs', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testListInputs */
    public function testUpdateInput()
    {
        $output = $this->runFunctionSnippet('update_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testUpdateInput */
    public function testGetInput()
    {
        $output = $this->runFunctionSnippet('get_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testGetInput */
    public function testDeleteInput()
    {
        $output = $this->runFunctionSnippet('delete_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString('Deleted input', $output);
    }

    private static function deleteOldInputs(): void
    {
        $livestreamClient = new LivestreamServiceClient();
        $parent = $livestreamClient->locationName(self::$projectId, self::$location);
        $response = $livestreamClient->listInputs($parent);
        $inputs = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($inputs as $input) {
            $tmp = explode('/', $input->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                try {
                    $livestreamClient->deleteInput($input->getName());
                } catch (ApiException $e) {
                    // Cannot delete inputs that are added to channels
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.', $input->getName());
                        continue;
                    }
                    throw $e;
                }
            }
        }
    }
}
