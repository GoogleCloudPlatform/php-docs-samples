<?php

/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Samples\Dlp;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for dlp commands.
 */
class dlpTest extends TestCase
{
    use TestTrait;
    use RetryTrait;

    public function testInspectImageFile()
    {
        $output = $this->runFunctionSnippet('inspect_image_file', [
            self::$projectId,
            __DIR__ . '/data/test.png'
        ]);

        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectTextFile()
    {
        $output = $this->runFunctionSnippet('inspect_text_file', [
            self::$projectId,
            __DIR__ . '/data/test.txt'
        ]);

        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectString()
    {
        $output = $this->runFunctionSnippet('inspect_string', [
            self::$projectId,
            'My name is Gary Smith and my email is gary@example.com'
        ]);

        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testListInfoTypes()
    {
        // list all info types
        $output = $this->runFunctionSnippet('list_info_types');

        $this->assertStringContainsString('US_DEA_NUMBER', $output);
        $this->assertStringContainsString('AMERICAN_BANKERS_CUSIP_ID', $output);

        // list info types with a filter
        $output = $this->runFunctionSnippet(
            'list_info_types',
            ['supported_by=RISK_ANALYSIS']
        );
        $this->assertStringContainsString('AGE', $output);
        $this->assertStringNotContainsString('AMERICAN_BANKERS_CUSIP_ID', $output);
    }

    public function testRedactImage()
    {
        $imagePath = __DIR__ . '/data/test.png';
        $outputPath = __DIR__ . '/data/redact.output.png';

        $output = $this->runFunctionSnippet('redact_image', [
            self::$projectId,
            $imagePath,
            $outputPath,
        ]);
        $this->assertNotEquals(
            sha1_file($outputPath),
            sha1_file($imagePath)
        );
    }

    public function testDeidentifyMask()
    {
        $numberToMask = 5;
        $output = $this->runFunctionSnippet('deidentify_mask', [
            self::$projectId,
            'My SSN is 372819127.',
            $numberToMask,
        ]);
        $this->assertStringContainsString('My SSN is xxxxx9127', $output);
    }

    public function testDeidentifyDates()
    {
        $keyName = $this->requireEnv('DLP_DEID_KEY_NAME');
        $wrappedKey = $this->requireEnv('DLP_DEID_WRAPPED_KEY');
        $inputCsv = __DIR__ . '/data/dates.csv';
        $outputCsv = __DIR__ . '/data/results.temp.csv';
        $dateFields = 'birth_date,register_date';
        $lowerBoundDays = 5;
        $upperBoundDays = 5;
        $contextField = 'name';

        $output = $this->runFunctionSnippet('deidentify_dates', [
            self::$projectId,
            $inputCsv,
            $outputCsv,
            $dateFields,
            $lowerBoundDays,
            $upperBoundDays,
            $contextField,
            $keyName,
            $wrappedKey,
        ]);

        $this->assertNotEquals(
            sha1_file($inputCsv),
            sha1_file($outputCsv)
        );

        $this->assertEquals(
            file($inputCsv)[0],
            file($outputCsv)[0]
        );

        unlink($outputCsv);
    }

    public function testDeidReidFPE()
    {
        $keyName = $this->requireEnv('DLP_DEID_KEY_NAME');
        $wrappedKey = $this->requireEnv('DLP_DEID_WRAPPED_KEY');
        $string = 'My SSN is 372819127.';
        $surrogateType = 'SSN_TOKEN';

        $deidOutput = $this->runFunctionSnippet('deidentify_fpe', [
            self::$projectId,
            $string,
            $keyName,
            $wrappedKey,
            $surrogateType,
        ]);
        $this->assertRegExp('/My SSN is SSN_TOKEN\(9\):\d+/', $deidOutput);

        $reidOutput = $this->runFunctionSnippet('reidentify_fpe', [
            self::$projectId,
            $deidOutput,
            $keyName,
            $wrappedKey,
            $surrogateType,
        ]);
        $this->assertStringContainsString($string, $reidOutput);
    }

    public function testTriggers()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        // Use a different bucket for triggers so we don't trigger a bunch of
        // DLP jobs on our actual storage bucket. This will create the trigger
        // on a nonexistant bucket.
        $bucketName .= '-dlp-triggers';

        $displayName = uniqid('My trigger display name ');
        $description = uniqid('My trigger description ');
        $triggerId = uniqid('my-php-test-trigger-');
        $scanPeriod = 1;
        $autoPopulateTimespan = true;

        $output = $this->runFunctionSnippet('create_trigger', [
            self::$projectId,
            $bucketName,
            $triggerId,
            $displayName,
            $description,
            $scanPeriod,
            $autoPopulateTimespan,
        ]);
        $fullTriggerId = sprintf('projects/%s/locations/global/jobTriggers/%s', self::$projectId, $triggerId);
        $this->assertStringContainsString('Successfully created trigger ' . $fullTriggerId, $output);

        $output = $this->runFunctionSnippet('list_triggers', [self::$projectId]);
        $this->assertStringContainsString('Trigger ' . $fullTriggerId, $output);
        $this->assertStringContainsString('Display Name: ' . $displayName, $output);
        $this->assertStringContainsString('Description: ' . $description, $output);
        $this->assertStringContainsString('Auto-populates timespan config: yes', $output);

        $output = $this->runFunctionSnippet('delete_trigger', [
            self::$projectId,
            $triggerId
        ]);
        $this->assertStringContainsString('Successfully deleted trigger ' . $fullTriggerId, $output);
    }

    public function testInspectTemplates()
    {
        $displayName = uniqid('My inspect template display name ');
        $description = uniqid('My inspect template description ');
        $templateId = uniqid('my-php-test-inspect-template-');
        $fullTemplateId = sprintf('projects/%s/locations/global/inspectTemplates/%s', self::$projectId, $templateId);

        $output = $this->runFunctionSnippet('create_inspect_template', [
            self::$projectId,
            $templateId,
            $displayName,
            $description
        ]);
        $this->assertStringContainsString('Successfully created template ' . $fullTemplateId, $output);

        $output = $this->runFunctionSnippet('list_inspect_templates', [self::$projectId]);
        $this->assertStringContainsString('Template ' . $fullTemplateId, $output);
        $this->assertStringContainsString('Display Name: ' . $displayName, $output);
        $this->assertStringContainsString('Description: ' . $description, $output);

        $output = $this->runFunctionSnippet('delete_inspect_template', [
            self::$projectId,
            $templateId
        ]);
        $this->assertStringContainsString('Successfully deleted template ' . $fullTemplateId, $output);
    }

    /**
     * @retryAttempts 3
     */
    public function testJobs()
    {
        // Set filter to only go back a day, so that we do not pull every job.
        $filter = sprintf(
            'state=DONE AND end_time>"%sT00:00:00+00:00"',
            date('Y-m-d', strtotime('-1 day'))
        );
        $jobIdRegex = "~projects/.*/dlpJobs/i-\d+~";

        $output = $this->runFunctionSnippet('list_jobs', [
            self::$projectId,
            $filter,
        ]);

        $this->assertRegExp($jobIdRegex, $output);
        preg_match($jobIdRegex, $output, $jobIds);
        $jobId = $jobIds[0];

        $output = $this->runFunctionSnippet(
            'delete_job',
            [$jobId]
        );
        $this->assertStringContainsString('Successfully deleted job ' . $jobId, $output);
    }
}
