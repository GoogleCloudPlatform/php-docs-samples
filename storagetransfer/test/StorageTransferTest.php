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

namespace Google\Cloud\Samples\StorageTransfer;

use DateTime;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\StorageTransfer\V1\Client\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\GetGoogleServiceAccountRequest;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\StorageTransfer\V1\UpdateTransferJobRequest;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class StorageTransferTest extends TestCase
{
    use TestTrait;

    private static $sts;
    private static $root;
    private static $storage;
    private static $sourceBucket;
    private static $sinkBucket;
    private static $sourceAgentPoolName;

    public static function setUpBeforeClass(): void
    {
        self::$root = sys_get_temp_dir();
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
        self::$sourceAgentPoolName = '';

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
        $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);

        preg_match('/transferJobs\/\d+/', $output, $match);
        self::deleteTransferJob($match[0]);
    }

    public function testCheckLatestTransferOperation()
    {
        $transferData = $this->runFunctionSnippet('quickstart', [
            self::$projectId, self::$sinkBucket->name(), self::$sourceBucket->name()
        ]);
        preg_match('/transferJobs\/\d+/', $transferData, $match);
        $jobName = $match[0];

        $output = $this->runFunctionSnippet('check_latest_transfer_operation', [
            self::$projectId, $jobName
        ]);

        $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);

        preg_match('/transferJobs\/\d+/', $output, $match);
        self::deleteTransferJob($match[0]);
    }

    public function testNearlineRequest()
    {
        $description = sprintf('My transfer job from %s -> %s', self::$sourceBucket->name(), self::$sinkBucket->name());
        $date = new DateTime('now');
        $startDate = $date->format('Y-m-d H:i:s');

        $output = $this->runFunctionSnippet('nearline_request', [
            self::$projectId, $description, self::$sourceBucket->name(), self::$sinkBucket->name(), $startDate
        ]);

        $this->assertMatchesRegularExpression('/Created and ran transfer job : transferJobs\/.*/', $output);

        preg_match('/transferJobs\/\d+/', $output, $match);
        self::deleteTransferJob($match[0]);
    }

    public function testManifestRequest()
    {
        try {
            $manifestName = 'manifest.csv';
            $rootDirectory = self::$root . '/sts-manifest-request-test';
            if (!is_dir($rootDirectory)) {
                mkdir($rootDirectory, 0700, true);
            }
            $tempFile = $rootDirectory . '/text.txt';

            // Write test data to the temporary file
            $testData = 'test data';
            file_put_contents($tempFile, $testData);

            // Escape double quotes for CSV content
            $csvContent = '"' . str_replace('"', '""', 'text.txt') . '"';
            $tempManifestObject = fopen('php://temp', 'r+'); // Create a temporary file stream

            // Write CSV content to the temporary manifest
            fwrite($tempManifestObject, $csvContent);

            // Upload the temporary manifest to GCS bucket (replace with your library)
            self::$sinkBucket->upload(
                $tempManifestObject,
                [
                    'name' => $manifestName
                ]
            );
            $manifestLocation = sprintf('gs://%s/%s', self::$sinkBucket->name(), $manifestName);

            $output = $this->runFunctionSnippet('manifest_request', [
                self::$projectId, self::$sourceAgentPoolName, $rootDirectory, self::$sinkBucket->name(), $manifestLocation
            ]);

            $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);
        } finally {
            unlink($tempFile);
            rmdir($rootDirectory);
            self::$sinkBucket->object($manifestName)->delete();
            preg_match('/transferJobs\/\w+/', $output, $match);
            self::deleteTransferJob($match[0]);
        }
    }

    public function testPosixRequest()
    {
        try {
            $rootDirectory = self::$root . '/sts-manifest-request-test';
            if (!is_dir($rootDirectory)) {
                mkdir($rootDirectory, 0700, true);
            }
            $tempFile = $rootDirectory . '/text.txt';

            // Write test data to the temporary file
            $testData = 'test data';
            file_put_contents($tempFile, $testData);

            $output = $this->runFunctionSnippet('posix_request', [
                self::$projectId, self::$sourceAgentPoolName, $rootDirectory, self::$sinkBucket->name()
            ]);

            $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);
        } finally {
            unlink($tempFile);
            rmdir($rootDirectory);
            preg_match('/transferJobs\/\w+/', $output, $match);
            self::deleteTransferJob($match[0]);
        }
    }

    public function testPosixToPosixRequest()
    {
        try {
            $sinkAgentPoolName = '';
            $rootDirectory = self::$root . '/sts-posix-test-source';
            $destinationDirectory = self::$root . '/sts-posix-test-sink';
            if (!is_dir($rootDirectory)) {
                mkdir($rootDirectory, 0700, true);
            }
            if (!is_dir($destinationDirectory)) {
                mkdir($destinationDirectory, 0700, true);
            }
            $tempFile = $rootDirectory . '/text.txt';

            // Write test data to the temporary file
            $testData = 'test data';
            file_put_contents($tempFile, $testData);

            $output = $this->runFunctionSnippet('posix_to_posix_request', [
                self::$projectId, self::$sourceAgentPoolName, $sinkAgentPoolName, $rootDirectory, $destinationDirectory, self::$sinkBucket->name()
            ]);

            $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);
        } finally {
            unlink($tempFile);
            rmdir($rootDirectory);
            rmdir($destinationDirectory);
            preg_match('/transferJobs\/\w+/', $output, $match);
            self::deleteTransferJob($match[0]);
        }
    }

    public function testDownloadToPosix()
    {
        try {
            $tempFileName = 'text.txt';
            $sinkAgentPoolName = '';
            $rootDirectory = self::$root . '/sts-download-to-posix-test';
            $gcsSourcePath = 'sts-manifest-request-test/';
            if (!is_dir($rootDirectory)) {
                mkdir($rootDirectory, 0700, true);
            }
            $tempFile = $rootDirectory . '/' . $tempFileName;
            file_put_contents($tempFile, 'test data');

            // Upload the temporary file to GCS
            self::$sourceBucket->upload(
                fopen($tempFile, 'r'),
                [
                    'name' => $tempFileName
                ]
            );

            $output = $this->runFunctionSnippet('posix_download', [
                self::$projectId, $sinkAgentPoolName, self::$sourceBucket->name(), $gcsSourcePath, $rootDirectory
            ]);

            $this->assertMatchesRegularExpression('/transferJobs\/.*/', $output);
        } finally {
            unlink($tempFile);
            rmdir($rootDirectory);
            self::$sourceBucket->object($tempFileName)->delete();
            preg_match('/transferJobs\/\w+/', $output, $match);
            self::deleteTransferJob($match[0]);
        }
    }

    // deletes a transfer job created by a sample to clean up
    private static function deleteTransferJob($jobName)
    {
        $transferJob = new TransferJob([
            'name' => $jobName,
            'status' => Status::DELETED
        ]);
        $request = (new UpdateTransferJobRequest())
            ->setJobName($jobName)
            ->setProjectId(self::$projectId)
            ->setTransferJob($transferJob);

        self::$sts->updateTransferJob($request);
    }

    private static function grantStsPermissions($bucket)
    {
        $request2 = (new GetGoogleServiceAccountRequest())
            ->setProjectId(self::$projectId);
        $googleServiceAccount = self::$sts->getGoogleServiceAccount($request2);
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
