<?php

/**
 * Copyright 2025 Google Inc.
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

namespace Google\Cloud\Samples\StorageBatchOperations;

use Google\Cloud\StorageBatchOperations\V1\Client\StorageBatchOperationsClient;
use Google\Cloud\StorageBatchOperations\V1\GetJobRequest;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for storage batch operations library samples.
 */
class StorageBatchOperationsTest extends TestCase
{
    use TestTrait;

    private static $bucket;
    private static $jobId;
    private static $jobName;
    private static $parent;
    private static $storage;
    private static $objectPrefix;
    private static $storageBatchOperationsClient;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        $uniqueBucketId = time() . rand();
        self::$storageBatchOperationsClient = new StorageBatchOperationsClient();
        self::$jobId = time() . rand();
        self::$objectPrefix = 'dummy';

        self::$parent = self::$storageBatchOperationsClient->locationName(self::$projectId, 'global');
        self::$jobName = self::$parent . '/jobs/' . self::$jobId;

        self::$bucket = self::$storage->createBucket(sprintf('php-gcs-sbo-sample-%s', $uniqueBucketId));

        $objectName = self::$objectPrefix . '-object-1.txt';
        self::$bucket->upload('test content', ['name' => $objectName]);

    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$bucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$bucket->delete();
    }

    public function testCreateJob()
    {
        $output = $this->runFunctionSnippet('create_job', [
            self::$projectId, self::$jobId, self::$bucket->name(), self::$objectPrefix
        ]);

        $this->assertStringContainsString(
            sprintf('Created job: %s', self::$parent),
            $output
        );
    }

    /**
     * @depends testCreateJob
     */
    public function testGetJob()
    {
        $output = $this->runFunctionSnippet('get_job', [
            self::$projectId, self::$jobId
        ]);

        $this->assertStringContainsString(
            self::$jobName,
            $output
        );
    }

    /**
     * @depends testGetJob
     */
    public function testListJobs()
    {
        $output = $this->runFunctionSnippet('list_jobs', [
            self::$projectId
        ]);

        $this->assertStringContainsString(
            self::$jobName,
            $output
        );
    }

    /**
     * @depends testListJobs
     */
    public function testCancelJob()
    {
        $output = $this->runFunctionSnippet('cancel_job', [
            self::$projectId, self::$jobId
        ]);

        $this->assertStringContainsString(
            sprintf('Cancelled job: %s', self::$jobName),
            $output
        );
    }

    /**
     * @depends testCancelJob
     */
    public function testDeleteJob()
    {
        $attempt = 0;
        $maxAttempts = 10;
        $jobReadyForDeletion = false;
        while ($attempt < $maxAttempts && !$jobReadyForDeletion) {
            $attempt++;
            $request = new GetJobRequest([
                'name' => self::$jobName,
            ]);

            $response = self::$storageBatchOperationsClient->getJob($request);
            $state = $response->getState();
            $status = \Google\Cloud\StorageBatchOperations\V1\Job\State::name($state);

            // A job is typically deletable if it's not in a creating/pending/running state
            // Consider PENDING or IN_PROGRESS as states to wait out.
            // For immediate deletion, maybe it needs to be SUCCEEDED or FAILED or CANCELED.
            if ($status !== 'STATE_UNSPECIFIED' && $status !== 'RUNNING') {
                $jobReadyForDeletion = true;
            }

            if (!$jobReadyForDeletion && $attempt < $maxAttempts) {
                sleep(10);   // Wait 10 seconds
            }
        }

        if (!$jobReadyForDeletion) {
            $this->fail("Job did not reach a deletable state within the allowed time.");
        }

        // Now attempt to delete the job
        $output = $this->runFunctionSnippet('delete_job', [
            self::$projectId, self::$jobId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted job: %s', self::$jobName),
            $output
        );
    }
}
