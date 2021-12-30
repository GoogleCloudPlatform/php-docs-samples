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

/**
 * Unit Tests for Transcoder commands.
 */
class transcoderTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $projectNumber;
    private static $storage;
    private static $bucket;
    private static $location = 'us-central1';

    private static $testVideoFileName = 'ChromeCast.mp4';
    private static $testOverlayImageFileName = 'overlay.jpg';
    private static $testConcatVideo1FileName = 'ForBiggerEscapes.mp4';
    private static $testConcatVideo2FileName = 'ForBiggerJoyrides.mp4';

    private static $inputVideoUri;
    private static $inputConcatVideo1Uri;
    private static $inputConcatVideo2Uri;
    private static $inputOverlayUri;
    private static $outputUriForPreset;
    private static $outputUriForAdHoc;
    private static $outputUriForTemplate;
    private static $outputUriForAnimatedOverlay;
    private static $outputUriForStaticOverlay;
    private static $outputUriForPeriodicImagesSpritesheet;
    private static $outputUriForSetNumberImagesSpritesheet;
    private static $outputUriForConcat;
    private static $preset = 'preset/web-hd';

    private static $jobIdRegex;
    private static $jobDeletedResponse = 'Deleted job' . PHP_EOL;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectNumber = self::requireEnv('GOOGLE_PROJECT_NUMBER');
        $bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');

        self::$storage = new StorageClient();
        self::$bucket = self::$storage->bucket($bucketName);
        foreach (self::$bucket->objects() as $object) {
            $object->delete();
        }

        $file = fopen(__DIR__ . '/data/' . self::$testVideoFileName, 'r');
        self::$bucket->upload($file, [
            'name' => self::$testVideoFileName
        ]);

        $file = fopen(__DIR__ . '/data/' . self::$testConcatVideo1FileName, 'r');
        self::$bucket->upload($file, [
            'name' => self::$testConcatVideo1FileName
        ]);

        $file = fopen(__DIR__ . '/data/' . self::$testConcatVideo2FileName, 'r');
        self::$bucket->upload($file, [
            'name' => self::$testConcatVideo2FileName
        ]);

        $file = fopen(__DIR__ . '/data/' . self::$testOverlayImageFileName, 'r');
        self::$bucket->upload($file, [
            'name' => self::$testOverlayImageFileName
        ]);

        self::$inputVideoUri = sprintf('gs://%s/%s', $bucketName, self::$testVideoFileName);
        self::$inputConcatVideo1Uri = sprintf('gs://%s/%s', $bucketName, self::$testConcatVideo1FileName);
        self::$inputConcatVideo2Uri = sprintf('gs://%s/%s', $bucketName, self::$testConcatVideo2FileName);
        self::$inputOverlayUri = sprintf('gs://%s/%s', $bucketName, self::$testOverlayImageFileName);
        self::$outputUriForPreset = sprintf('gs://%s/test-output-preset/', $bucketName);
        self::$outputUriForAdHoc = sprintf('gs://%s/test-output-adhoc/', $bucketName);
        self::$outputUriForTemplate = sprintf('gs://%s/test-output-template/', $bucketName);
        self::$outputUriForAnimatedOverlay = sprintf('gs://%s/test-output-animated-overlay/', $bucketName);
        self::$outputUriForStaticOverlay = sprintf('gs://%s/test-output-static-overlay/', $bucketName);
        self::$outputUriForPeriodicImagesSpritesheet = sprintf('gs://%s/test-output-periodic-spritesheet/', $bucketName);
        self::$outputUriForSetNumberImagesSpritesheet = sprintf('gs://%s/test-output-set-number-spritesheet/', $bucketName);
        self::$outputUriForConcat = sprintf('gs://%s/test-output-concat/', $bucketName);

        self::$jobIdRegex = sprintf('~projects/%s/locations/%s/jobs/~', self::$projectNumber, self::$location);
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$bucket->objects() as $object) {
            $object->delete();
        }
    }

    public function assertJobStateSucceeded($jobId)
    {
        $this->runEventuallyConsistentTest(function () use ($jobId) {
            $output = $this->runFunctionSnippet('get_job_state', [
               self::$projectId,
               self::$location,
               $jobId
            ]);
            $this->assertStringContainsString('Job state: SUCCEEDED' . PHP_EOL, $output);
        }, 5, true);
    }

    public function testJobTemplate()
    {
        $jobTemplateId = sprintf('php-test-template-%s', time());
        $jobTemplateName = sprintf('projects/%s/locations/%s/jobTemplates/%s', self::$projectNumber, self::$location, $jobTemplateId);

        $output = $this->runFunctionSnippet('create_job_template', [
            self::$projectId,
            self::$location,
            $jobTemplateId
        ]);
        $this->assertStringContainsString($jobTemplateName, $output);

        $output = $this->runFunctionSnippet('get_job_template', [
            self::$projectId,
            self::$location,
            $jobTemplateId
        ]);
        $this->assertStringContainsString($jobTemplateName, $output);

        $output = $this->runFunctionSnippet('list_job_templates', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString($jobTemplateName, $output);

        $output = $this->runFunctionSnippet('delete_job_template', [
            self::$projectId,
            self::$location,
            $jobTemplateId
        ]);
        $this->assertStringContainsString('Deleted job template' . PHP_EOL, $output);
    }

    public function testJobFromAdHoc()
    {
        $createOutput = $this->runFunctionSnippet('create_job_from_ad_hoc', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$outputUriForAdHoc
        ]);
        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $createOutput);

        $jobId = explode('/', $createOutput);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        // Test Get method
        $getOutput = $this->runFunctionSnippet('get_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
        $this->assertStringContainsString($createOutput, $getOutput);

        // Test List method
        $listOutput = $this->runFunctionSnippet('list_jobs', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString($jobId, $listOutput);

        // Test Delete method
        $deleteOutput = $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
        $this->assertStringContainsString('Deleted job' . PHP_EOL, $deleteOutput);
    }

    public function testJobFromPreset()
    {
        $output = $this->runFunctionSnippet('create_job_from_preset', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$outputUriForPreset,
            self::$preset
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }

    public function testJobFromTemplate()
    {
        $jobTemplateId = sprintf('php-test-template-%s', time());
        $this->runFunctionSnippet('create_job_template', [
            self::$projectId,
            self::$location,
            $jobTemplateId
        ]);

        $output = $this->runFunctionSnippet('create_job_from_template', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$outputUriForTemplate,
            $jobTemplateId
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);

        $this->runFunctionSnippet('delete_job_template', [
            self::$projectId,
            self::$location,
            $jobTemplateId
        ]);
    }

    public function testJobAnimatedOverlay()
    {
        $output = $this->runFunctionSnippet('create_job_with_animated_overlay', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$inputOverlayUri,
            self::$outputUriForAnimatedOverlay
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }

    public function testJobStaticOverlay()
    {
        $output = $this->runFunctionSnippet('create_job_with_static_overlay', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$inputOverlayUri,
            self::$outputUriForStaticOverlay
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }

    public function testJobPeriodicImagesSpritesheet()
    {
        $output = $this->runFunctionSnippet('create_job_with_periodic_images_spritesheet', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$outputUriForPeriodicImagesSpritesheet
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }

    public function testJobSetNumberImagesSpritesheet()
    {
        $output = $this->runFunctionSnippet('create_job_with_set_number_images_spritesheet', [
            self::$projectId,
            self::$location,
            self::$inputVideoUri,
            self::$outputUriForSetNumberImagesSpritesheet
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }

    public function testJobConcat()
    {
        $output = $this->runFunctionSnippet('create_job_with_concatenated_inputs', [
            self::$projectId,
            self::$location,
            self::$inputConcatVideo1Uri,
            0,
            8.1,
            self::$inputConcatVideo2Uri,
            3.5,
            15,
            self::$outputUriForConcat
        ]);

        $this->assertRegExp(sprintf('%s', self::$jobIdRegex), $output);

        $jobId = explode('/', $output);
        $jobId = trim($jobId[(count($jobId) - 1)]);

        sleep(30);
        $this->assertJobStateSucceeded($jobId);

        $this->runFunctionSnippet('delete_job', [
            self::$projectId,
            self::$location,
            $jobId
        ]);
    }
}
