<?php
/**
 * Copyright 2021 Google Inc.
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

namespace Google\Cloud\Samples\StorageTransfer;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\StorageTransfer\V1\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use PHPUnit\Framework\TestCase;

class StorageTransferTest extends TestCase
{
    use TestTrait;

    private static $sourceBucket;
    private static $sinkBucket;
    private static $storage;
    private static $sts;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$sts = new StorageTransferServiceClient();
        $uniqueBucketId = time() . rand();
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-source-bucket-%s', $uniqueBucketId)
        );
        self::$sinkBucket = self::$storage->createBucket(
            sprintf('php-sink-bucket-%s', $uniqueBucketId)
        );

        self::grantStsPermissions(self::$sourceBucket);
        self::grantStsPermissions(self::$sinkBucket);
    }

    public static function tearDownAfterClass(): void
    {
        self::$sourceBucket->delete();
        self::$sinkBucket->delete();
    }

    public function testQuickstart()
    {
        $output = $this->runFunctionSnippet('quickstart', [
            self::$projectId, self::$sinkBucket->name(), self::$sourceBucket->name()
        ]);

        $this->assertRegExp('/transferJobs\/.*/', $output);

        preg_match('/transferJobs\/\d+/', $output, $match);
        $jobName = $match[0];
        $transferJob = new TransferJob([
            'name' => $jobName,
            'status' => Status::DELETED
        ]);

        self::$sts->updateTransferJob($jobName, self::$projectId, $transferJob);
    }

    private static function grantStsPermissions($bucket)
    {
        $googleServiceAccount = self::$sts->getGoogleServiceAccount(self::$projectId);
        $email = $googleServiceAccount->getAccountEmail();
        $members = ['serviceAccount:' . $email];

        $policy = $bucket->iam()->policy(['requestedPolicyVersion' => 3]);
        $policy['version'] = 3;

        array_push(
            $policy['bindings'],
            ['role' => 'roles/storage.objectViewer', 'members' => $members],
            ['role' => 'roles/storage.legacyBucketReader', 'members' => $members],
            ['role' => 'roles/storage.legacyBucketWriter', 'members' => $members]
        );

        $bucket->iam()->setPolicy($policy);
    }
}
