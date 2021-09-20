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
declare(strict_types=1);

namespace Google\Cloud\Samples\Media\Transcoder;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for Transcoder commands.
 */
// run ../../testing/vendor/bin/phpunit for testing

class transcoderTest extends TestCase
{
    use TestTrait;

    use EventuallyConsistentTestTrait;


    private static $bucketName;
    private static $projectNumber;

    private static $storage;
    private static $tempBucket;
    private static $location = 'us-central1';
    private static $testVideoFileName = 'ChromeCast.mp4';
    private static $testOverlayImageFileName = 'overlay.jpg';

    private static $inputUri;
    private static $outputUriForPreset;
    private static $preset = 'preset/web-hd';

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$projectNumber = self::requireEnv('PROJECT_NUMBER');

        self::$storage = new StorageClient();
        self::$tempBucket = self::$storage->createBucket(
            sprintf('%s-php-test-bucket-%s', self::$projectId, time())
        );

        $file = fopen(__DIR__ . '/data/' . self::$testVideoFileName, 'r');
        self::$tempBucket->upload($file, [
            'name' => self::$testVideoFileName
        ]);

        $file = fopen(__DIR__ . '/data/' . self::$testOverlayImageFileName, 'r');
        self::$tempBucket->upload($file, [
            'name' => self::$testOverlayImageFileName
        ]);

        self::$inputUri = sprintf('gs://%s/%s', self::$bucketName, self::$testVideoFileName);
        self::$outputUriForPreset = sprintf('gs://%s/test-output-preset/', self::$bucketName);

    }

   public static function tearDownAfterClass(): void
   {
       foreach (self::$tempBucket->objects() as $object) {
           $object->delete();
       }
       self::$tempBucket->delete();
   }

   public function assertJobStateSucceeded($jobId)
   {
       $this->runEventuallyConsistentTest(function () use ($jobId) {
           $output = $this->runSnippet('get_job_state', [
               self::$projectId,
               self::$location,
               $jobId
           ]);

           $this->assertStringContainsString('Job state: SUCCEEDED', $output);
       }, 5, true);
   }

    public function testJobFromPreset()
    {
        $jobIdRegex = sprintf('~projects/%s/locations/%s/jobs/~', self::$projectNumber, self::$location);

        $output = $this->runSnippet('create_job_from_preset', [
            self::$projectId,
            self::$location,
            self::$inputUri,
            self::$outputUriForPreset,
            self::$preset
        ]);

        $this->assertRegExp(sprintf('%s', $jobIdRegex), $output);
        $jobId = explode("/", $output);
        $jobId = $jobId[(count($jobId) - 1)];
        sleep(10);

        $this->runEventuallyConsistentTest(function ()  use ($jobId){
            $output = $this->runFunctionSnippet('get_job_state', [
                self::$projectId,
                self::$location,
                $jobId,
            ]);

            $this->assertStringContainsString('Job state: SUCCEEDED', $output);
        }, 5, true);
//        $this->assertJobStateSucceeded($jobId);

        printf('Job ID: %s' . PHP_EOL, $jobId);


//        $output = $this->runSnippet(
//            'delete_job',
//            [$jobId]
//        );
//        $this->assertStringContainsString('Successfully deleted job ' . $jobId, $output);
    }
}
