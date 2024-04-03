<?php
/**
 * Copyright 2024 Google Inc.
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

namespace Google\Cloud\Samples\StorageInsights;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for storage commands.
 */
class StorageInsightsTest extends TestCase
{
    use TestTrait;

    private static $sourceBucket;
    private static $sinkBucket;
    private static $storage;
    private static $location;
    private static $reportUuid;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$location = 'us-west1';
        $uniqueBucketId = time() . rand();
        $lifecycle = Bucket::lifecycle()
            ->addDeleteRule([
                'age' => 50,
                'isLive' => true
            ]);
        ;
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-gcsinsights-src-bkt-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'lifecycle' => $lifecycle,
                // 'userProject' =>
            ]
        );
        self::setIamPolicy(self::$sourceBucket);
        self::$sinkBucket = self::$storage->createBucket(
            sprintf('php-gcsinsights-sink-bkt-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'lifecycle' => $lifecycle,
                'storageClass' => 'NEARLINE'
            ]
        );
        self::setIamPolicy(self::$sinkBucket);
        // time needed for IAM policy to propagate
        sleep(5);
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$sourceBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$sourceBucket->delete();
        foreach (self::$sinkBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$sinkBucket->delete();
    }

    public function testCreateInventoryReportConfig()
    {
        $output = $this->runFunctionSnippet('create_inventory_report_config', [
            self::$projectId, self::$location, self::$sinkBucket->name(), self::$sourceBucket->name()
        ]);

        $this->assertStringContainsString(
            'Created inventory report config with name:',
            $output
        );
        $this->assertStringContainsString(
            'reportConfigs/',
            $output
        );

        self::$reportUuid = $this->getReportConfigNameFromSampleOutput($output);
    }

    /**
     * @depends testCreateInventoryReportConfig
     */
    public function testGetInventoryReportConfigs($output)
    {
        $output = $this->runFunctionSnippet('get_inventory_report_names', [
            self::$projectId, self::$location, self::$reportUuid
        ]);

        /* We can't actually test for a report config name because it takes 24 hours
        * for an inventory report to actually get written to the bucket.
        * We could set up a hard-coded bucket, but that would probably introduce flakes.
        * The best we can do is make sure the test runs without throwing an error.
        */
        $this->assertStringContainsString(
            'download the following objects from Google Cloud Storage:',
            $output
        );
    }

    /**
     * @depends testGetInventoryReportConfigs
     */
    public function testListInventoryReportConfigs()
    {
        $output = $this->runFunctionSnippet('list_inventory_report_configs', [
            self::$projectId, self::$location
        ]);

        $this->assertStringContainsString(
            sprintf('Inventory report configs in project %s and location %s:', self::$projectId, self::$location),
            $output
        );

        $this->assertStringContainsString(
            self::$reportUuid,
            $output
        );
    }

    /**
     * @depends testListInventoryReportConfigs
     */
    public function testEditInventoryReportConfigs()
    {
        $output = $this->runFunctionSnippet('edit_inventory_report_config', [
            self::$projectId, self::$location, self::$reportUuid
        ]);

        $this->assertStringContainsString('Edited inventory report config with name', $output);
    }

    /**
     * @depends testEditInventoryReportConfigs
     */
    public function testDeleteInventoryReportConfigs()
    {
        $output = $this->runFunctionSnippet('delete_inventory_report_config', [
            self::$projectId, self::$location, self::$reportUuid
        ]);

        $this->assertStringContainsString('Deleted inventory report config with name', $output);
    }

    private static function setIamPolicy($bucket)
    {
        $projectNumber = self::requireEnv('GOOGLE_PROJECT_NUMBER');
        $email = 'service-' . $projectNumber . '@gcp-sa-storageinsights.iam.gserviceaccount.com';
        $members = ['serviceAccount:' . $email];
        $policy = $bucket->iam()->policy(['requestedPolicyVersion' => 3]);
        $policy['version'] = 3;

        array_push(
            $policy['bindings'],
            ['role' => 'roles/storage.insightsCollectorService', 'members' => $members],
            ['role' => 'roles/storage.objectCreator', 'members' => $members],
        );

        $bucket->iam()->setPolicy($policy);
    }

    private function getReportConfigNameFromSampleOutput($output)
    {
        // report uuid is the second line of the output
        $reportName = explode("\n", trim($output))[1];
        // report name is of the format: projects/*/locations/*/reportConfigs/*
        $reportNameParts = explode("/", $reportName);
        return end($reportNameParts);
    }
}
