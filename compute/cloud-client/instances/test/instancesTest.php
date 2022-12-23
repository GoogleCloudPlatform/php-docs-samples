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

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class instancesTest extends TestCase
{
    use TestTrait;

    private static $instanceName;
    private static $instanceExists = false;
    private static $encInstanceName;
    private static $encInstanceExists = false;
    private static $encKey;
    private static $bucketName;
    private static $bucket;

    private const DEFAULT_ZONE = 'us-central1-a';

    public static function setUpBeforeClass(): void
    {
        self::$instanceName = sprintf('test-compute-instance-%s', rand());
        self::$encInstanceName = sprintf('test-compute-instance-customer-encryption-key-%s', rand());
        self::$encKey = base64_encode(random_bytes(32));

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

        // Make sure we delete any instances created in the process of testing - we don't care about response
        // because if everything went fine they should already be deleted
        if (self::$instanceExists) {
            self::runFunctionSnippet('delete_instance', [
                'projectId' => self::$projectId,
                'zone' => self::DEFAULT_ZONE,
                'instanceName' => self::$instanceName
            ]);
        }

        if (self::$encInstanceExists) {
            self::runFunctionSnippet('delete_instance', [
                'projectId' => self::$projectId,
                'zone' => self::DEFAULT_ZONE,
                'instanceName' => self::$encInstanceName
            ]);
        }
    }

    public function testCreateInstance()
    {
        $output = $this->runFunctionSnippet('create_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Created instance ' . self::$instanceName, $output);
        self::$instanceExists = true;
    }

    public function testCreateInstanceWithEncryptionKey()
    {
        $output = $this->runFunctionSnippet('create_instance_with_encryption_key', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$encInstanceName,
            'key' => self::$encKey
        ]);
        $this->assertStringContainsString('Created instance ' . self::$encInstanceName, $output);
        self::$encInstanceExists = true;
    }

    /**
     * @depends testCreateInstance
     */
    public function testListInstances()
    {
        $output = $this->runFunctionSnippet('list_instances', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE
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
     * @depends testListInstances
     * @depends testListAllInstances
     */
    public function testStopInstance()
    {
        $output = $this->runFunctionSnippet('stop_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$instanceName . ' stopped successfully', $output);
    }

    /**
     * @depends testStopInstance
     */
    public function testStartInstance()
    {
        $output = $this->runFunctionSnippet('start_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$instanceName . ' started successfully', $output);
    }

    /**
     * @depends testCreateInstanceWithEncryptionKey
     */
    public function testStartWithEncryptionKeyInstance()
    {
        // Stop instance
        $output = $this->runFunctionSnippet('stop_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$encInstanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$encInstanceName . ' stopped successfully', $output);

        // Restart instance with customer encryption key
        $output = $this->runFunctionSnippet('start_instance_with_encryption_key', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$encInstanceName,
            'key' => self::$encKey
        ]);
        $this->assertStringContainsString('Instance ' . self::$encInstanceName . ' started successfully', $output);
    }

    /**
     * @depends testStartInstance
     * @depends testStartWithEncryptionKeyInstance
     */
    public function testResetInstance()
    {
        $output = $this->runFunctionSnippet('reset_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$instanceName . ' reset successfully', $output);

        $output = $this->runFunctionSnippet('reset_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$encInstanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$encInstanceName . ' reset successfully', $output);
    }

    /**
     * @depends testCreateInstance
     */
    public function testSuspendInstance()
    {
        $output = $this->runFunctionSnippet('suspend_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$instanceName . ' suspended successfully', $output);
    }

    /**
     * @depends testSuspendInstance
     */
    public function testResumeInstance()
    {
        $output = $this->runFunctionSnippet('resume_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Instance ' . self::$instanceName . ' resumed successfully', $output);
    }

    /**
     * @depends testResumeInstance
     */
    public function testDeleteInstance()
    {
        $output = $this->runFunctionSnippet('delete_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$instanceName
        ]);
        $this->assertStringContainsString('Deleted instance ' . self::$instanceName, $output);
        self::$instanceExists = false;
    }

    /**
     * @depends testResumeInstance
     */
    public function testDeleteWithEncryptionKeyInstance()
    {
        $output = $this->runFunctionSnippet('delete_instance', [
            'projectId' => self::$projectId,
            'zone' => self::DEFAULT_ZONE,
            'instanceName' => self::$encInstanceName
        ]);
        $this->assertStringContainsString('Deleted instance ' . self::$encInstanceName, $output);
        self::$encInstanceExists = false;
    }

    public function testSetUsageExportBucketDefaultPrefix()
    {
        // Check default value behaviour for setter
        $output = $this->runFunctionSnippet('set_usage_export_bucket', [
            'projectId' => self::$projectId,
            'bucketName' => self::$bucketName
        ]);

        $this->assertStringContainsString('default value of `usage_gce`', $output);
        $this->assertStringContainsString('project `' . self::$projectId . '`', $output);
        $this->assertStringContainsString('bucket_name = `' . self::$bucketName . '`', $output);
        $this->assertStringContainsString('report_name_prefix = `usage_gce`', $output);

        // Check default value behaviour for getter
        $output = $this->runFunctionSnippet('get_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString('default value of `usage_gce`', $output);
        $this->assertStringContainsString('project `' . self::$projectId . '`', $output);
        $this->assertStringContainsString('bucket_name = `' . self::$bucketName . '`', $output);
        $this->assertStringContainsString('report_name_prefix = `usage_gce`', $output);

        // Disable usage exports
        $output = $this->runFunctionSnippet('disable_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString('project `' . self::$projectId . '` was disabled', $output);

        $output = $this->runFunctionSnippet('get_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString('project `' . self::$projectId . '` is disabled', $output);
    }

    public function testSetUsageExportBucketCustomPrefix()
    {
        // Set custom prefix
        $customPrefix = 'my-custom-prefix';

        // Check user value behaviour for setter
        $output = $this->runFunctionSnippet('set_usage_export_bucket', [
            'projectId' => self::$projectId,
            'bucketName' => self::$bucketName,
            'reportNamePrefix' => $customPrefix
        ]);

        $this->assertStringNotContainsString('default value of `usage_gce`', $output);
        $this->assertStringContainsString('project `' . self::$projectId . '`', $output);
        $this->assertStringContainsString('bucket_name = `' . self::$bucketName . '`', $output);
        $this->assertStringContainsString('report_name_prefix = `' . $customPrefix . '`', $output);

        // Check user value behaviour for getter
        $output = $this->runFunctionSnippet('get_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringNotContainsString('default value of `usage_gce`', $output);
        $this->assertStringContainsString('project `' . self::$projectId . '`', $output);
        $this->assertStringContainsString('bucket_name = `' . self::$bucketName . '`', $output);
        $this->assertStringContainsString('report_name_prefix = `' . $customPrefix . '`', $output);

        // Disable usage exports
        $output = $this->runFunctionSnippet('disable_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString('project `' . self::$projectId . '` was disabled', $output);

        $output = $this->runFunctionSnippet('get_usage_export_bucket', [
            'projectId' => self::$projectId
        ]);
        $this->assertStringContainsString('project `' . self::$projectId . '` is disabled', $output);
    }

    public function testListAllImages()
    {
        $output = $this->runFunctionSnippet('list_all_images', [
            'projectId' => 'windows-sql-cloud'
        ]);

        $this->assertStringContainsString('sql-2012-enterprise-windows', $output);
        $arr = explode(PHP_EOL, $output);
        $this->assertGreaterThanOrEqual(2, count($arr));
    }

    public function testListImagesByPage()
    {
        $output = $this->runFunctionSnippet('list_images_by_page', [
            'projectId' => 'windows-sql-cloud'
        ]);

        $this->assertStringContainsString('sql-2012-enterprise-windows', $output);
        $this->assertStringContainsString('Page 2', $output);
        $arr = explode(PHP_EOL, $output);
        $this->assertGreaterThanOrEqual(2, count($arr));
    }
}
