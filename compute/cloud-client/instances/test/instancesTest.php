<?php
/**
 * Copyright 2021 Google LLC
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

namespace Google\Cloud\Samples\Compute;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class instancesTest extends TestCase
{
    use TestTrait;

    private static $instanceName;
    private static $instanceNameWait;

    private const DEFAULT_ZONE = 'us-central1-a';
    private const MACHINE_TYPE = 'n1-standard-1';
    private const SOURCE_IMAGE = 'projects/debian-cloud/global/images/family/debian-10';
    private const NETWORK_NAME = 'global/networks/default';

    public static function setUpBeforeClass(): void
    {
        self::$instanceName = sprintf('test-compute-instance-%s', rand());
        self::$instanceNameWait = sprintf('test-compute-instance-%s', rand());
    }

    public function testCreateInstance()
    {
        $output = $this->runFunctionSnippet('create_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Created instance ' . self::$instanceName, $output);
    }

    /**
     * @depends testCreateInstance
     */
    public function testListInstances()
    {
        $output = $this->runFunctionSnippet('list_instances', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
        ]);
        $this->assertStringContainsString(self::$instanceName, $output);
    }

    /**
     * @depends testCreateInstance
     */
    public function testDeleteInstance()
    {
        $output = $this->runFunctionSnippet('delete_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName,
        ]);
        $this->assertStringContainsString('Deleted instance ' . self::$instanceName, $output);
    }

    /**
     * @group waitForOperation
     */
    public function testCreateInstanceWait()
    {
        // We need to provide all parameters, as runFunctionSnippet
        //  doesn't support named arguments
        $output = $this->runFunctionSnippet('create_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceNameWait,
            'machineType' => self::MACHINE_TYPE,
            'sourceImage' => self::SOURCE_IMAGE,
            'networkName' => self::NETWORK_NAME,
            'waitForOperation' => true
        ]);
        $this->assertStringContainsString('Created instance ' . self::$instanceNameWait, $output);
    }

    /**
     * @depends testCreateInstanceWait
     * @group waitForOperation
     */
    public function testListInstancesWait()
    {
        $output = $this->runFunctionSnippet('list_instances', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
        ]);
        $this->assertStringContainsString(self::$instanceNameWait, $output);
    }

    /**
     * @depends testCreateInstanceWait
     * @group waitForOperation
     */
    public function testDeleteInstanceWait()
    {
        $output = $this->runFunctionSnippet('delete_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceNameWait,
            'waitForOperation' => true
        ]);
        $this->assertStringContainsString('Deleted instance ' . self::$instanceNameWait, $output);
    }
}
