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

use Google\Cloud\Compute\V1\Operation;
use Google\Cloud\Compute\V1\GlobalOperationsClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class instancesTest extends TestCase
{
    use TestTrait;

    private static $instanceName;
    private static $bucketName;
    private static $bucket;

    private const DEFAULT_ZONE = 'us-central1-a';

    public static function setUpBeforeClass(): void
    {
        self::$instanceName = sprintf('test-compute-instance-%s', rand());

        // Generate bucket name
        self::$bucketName = sprintf('test-compute-usage-export-bucket-%s', rand());

        // Setup new bucket for UsageReports
        $storage = new StorageClient([
            'projectId' => self::$projectId
        ]);

        self::$bucket = $storage->createBucket(self::$bucketName);
    }

    public static function tearDownAfterClass(): void
    {
        // Remove the bucket
        self::$bucket->delete();
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
    public function testListAllInstances()
    {
        $output = $this->runFunctionSnippet('list_all_instances', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString(self::$instanceName, $output);
        $this->assertStringContainsString(self::DEFAULT_ZONE, $output);
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

    public function testSetUsageExportBucketDefaultPrefix()
    {
        // We include files directly as we need access to returned objects
        require_once "src/set_usage_export_bucket.php";
        require_once "src/get_usage_export_bucket.php";
        require_once "src/disable_usage_export_bucket.php";

        // Check default value behaviour for setter
        ob_start();
        $operation = set_usage_export_bucket(self::$projectId, self::$bucketName);
        $this->assertStringContainsString('default value of `usage_gce`', ob_get_clean());

        // Wait for the settings to take place
        if ($operation->getStatus() === Operation\Status::RUNNING) {
            // Wait until operation completes
            $operationClient = new GlobalOperationsClient();
            $operationClient->wait($operation->getName(), self::$projectId);
        }

        // Check default value behaviour for getter
        ob_start();
        $usageExportLocation = get_usage_export_bucket(self::$projectId);
        $this->assertStringContainsString('default value of `usage_gce`', ob_get_clean());
        $this->assertEquals($usageExportLocation->getBucketName(), self::$bucketName);
        $this->assertEquals($usageExportLocation->getReportNamePrefix(), 'usage_gce');

        // Disable usage exports
        $operation = disable_usage_export_bucket(self::$projectId);

        // Wait for the settings to take place
        if ($operation->getStatus() === Operation\Status::RUNNING) {
            // Wait until operation completes
            $operationClient = new GlobalOperationsClient();
            $operationClient->wait($operation->getName(), self::$projectId);
        }

        // Make sure the export bucket was properly disabled
        $usageExportLocation = get_usage_export_bucket(self::$projectId);
        $this->assertNull($usageExportLocation);
    }

    public function testSetUsageExportBucketCustomPrefix()
    {
        // We include files directly as we need access to returned objects
        require_once "src/set_usage_export_bucket.php";
        require_once "src/get_usage_export_bucket.php";
        require_once "src/disable_usage_export_bucket.php";

        // Set custom prefix
        $customPrefix = "my-custom-prefix";

        // Check user value behaviour for setter
        ob_start();
        $operation = set_usage_export_bucket(self::$projectId, self::$bucketName, $customPrefix);
        $this->assertStringNotContainsString('default value of `usage_gce`', ob_get_clean());

        // Wait for the settings to take place
        if ($operation->getStatus() === Operation\Status::RUNNING) {
            // Wait until operation completes
            $operationClient = new GlobalOperationsClient();
            $operationClient->wait($operation->getName(), self::$projectId);
        }

        // Check user value behaviour for getter
        ob_start();
        $usageExportLocation = get_usage_export_bucket(self::$projectId);
        $this->assertStringNotContainsString('default value of `usage_gce`', ob_get_clean());
        $this->assertEquals($usageExportLocation->getBucketName(), self::$bucketName);
        $this->assertEquals($usageExportLocation->getReportNamePrefix(), $customPrefix);

        // Disable usage exports
        $operation = disable_usage_export_bucket(self::$projectId);

        // Wait for the settings to take place
        if ($operation->getStatus() === Operation\Status::RUNNING) {
            // Wait until operation completes
            $operationClient = new GlobalOperationsClient();
            $operationClient->wait($operation->getName(), self::$projectId);
        }

        // Make sure the export bucket was properly disabled
        $usageExportLocation = get_usage_export_bucket(self::$projectId);
        $this->assertNull($usageExportLocation);
    }
}
