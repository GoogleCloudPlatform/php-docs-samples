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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for dlp commands.
 */
class dlpTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../dlp.php';

    public static function setUpBeforeClass()
    {
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');
    }

    public function setUp()
    {
        $this->useResourceExhaustedBackoff(5);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId], $params);
        ob_start();
        require __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }

    public function testInspectDatastore()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('inspect-datastore', [
            'kind' => 'Person',
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'namespace' => 'DLP',
            'calling-project' => self::$projectId,
        ]);
        $this->assertContains('PERSON_NAME', $output);
    }

    public function testInspectBigquery()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('inspect-bigquery', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId
        ]);
        $this->assertContains('PERSON_NAME', $output);
    }

    public function testInspectGCS()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $output = $this->runCommand('inspect-gcs', [
            'bucket-id' => $bucketName,
            'file' => 'dlp/harmful.csv',
            'calling-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId
        ]);
        $this->assertContains('PERSON_NAME', $output);
    }

    public function testInspectImageFile()
    {
        $output = $this->runSnippet('dlp_inspect_image_file', [
            __DIR__ . '/data/test.png'
        ]);

        $this->assertContains('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectTextFile()
    {
        $output = $this->runSnippet('dlp_inspect_text_file', [
            __DIR__ . '/data/test.txt'
        ]);

        $this->assertContains('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectString()
    {
        $output = $this->runSnippet('dlp_inspect_string', [
            "My name is Gary Smith and my email is gary@example.com"
        ]);

        $this->assertContains('Info type: EMAIL_ADDRESS', $output);
    }

    public function testListInfoTypes()
    {
        // list all info types
        $output = $this->runCommand('list-info-types');

        $this->assertContains('US_DEA_NUMBER', $output);
        $this->assertContains('AMERICAN_BANKERS_CUSIP_ID', $output);

        // list info types with a filter
        $output = $this->runCommand('list-info-types', [
            'filter' => 'supported_by=RISK_ANALYSIS'
        ]);
        $this->assertContains('AGE', $output);
        $this->assertNotContains('AMERICAN_BANKERS_CUSIP_ID', $output);
    }

    public function testRedactImage()
    {
        $output = $this->runCommand('redact-image', [
            'image-path' => dirname(__FILE__) . '/data/test.png',
            'output-path' => dirname(__FILE__) . '/data/redact.output.png'
        ]);
        $this->assertNotEquals(
            sha1_file(dirname(__FILE__) . '/data/redact.output.png'),
            sha1_file(dirname(__FILE__) . '/data/test.png')
        );
    }

    public function testDeidentifyMask()
    {
        $output = $this->runCommand('deidentify-mask', [
            'string' => 'My SSN is 372819127.',
            'number-to-mask' => 5
        ]);
        $this->assertContains('My SSN is xxxxx9127', $output);
    }

    public function testDeidentifyDates()
    {
        $this->requireEnv('DLP_DEID_KEY_NAME');
        $this->requireEnv('DLP_DEID_WRAPPED_KEY');

        $inputPath = dirname(__FILE__) . '/data/dates.csv';
        $outputPath = dirname(__FILE__) . '/data/results.temp.csv';

        $output = $this->runCommand('deidentify-dates', [
            'input-csv' => 'test/data/dates.csv',
            'output-csv' => 'test/data/results.temp.csv',
            'date-fields' => 'birth_date,register_date',
            'lower-bound-days' => 5,
            'upper-bound-days' => 5,
            'context-field' => 'name',
            'wrapped-key' => getenv('DLP_DEID_WRAPPED_KEY'),
            'key-name' => getenv('DLP_DEID_KEY_NAME')
        ]);

        $this->assertNotEquals(
            sha1_file($inputPath),
            sha1_file($outputPath)
        );

        $this->assertEquals(
            file($inputPath)[0],
            file($outputPath)[0]
        );

        unlink($outputPath);
    }

    public function testDeidReidFPE()
    {
        $this->requireEnv('DLP_DEID_KEY_NAME');
        $this->requireEnv('DLP_DEID_WRAPPED_KEY');

        $string = 'My SSN is 372819127.';

        $deidOutput = $this->runCommand('deidentify-fpe', [
            'string' => $string,
            'wrapped-key' => getenv('DLP_DEID_WRAPPED_KEY'),
            'key-name' => getenv('DLP_DEID_KEY_NAME'),
            'surrogate-type' => 'SSN_TOKEN'
        ]);
        $this->assertRegExp('/My SSN is SSN_TOKEN\(9\):\d+/', $deidOutput);

        $reidOutput = $this->runCommand('reidentify-fpe', [
            'string' => $deidOutput,
            'wrapped-key' => getenv('DLP_DEID_WRAPPED_KEY'),
            'key-name' => getenv('DLP_DEID_KEY_NAME'),
            'surrogate-type' => 'SSN_TOKEN'
        ]);
        $this->assertContains($string, $reidOutput);
    }

    public function testTriggers()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $displayName = uniqid("My trigger display name ");
        $description = uniqid("My trigger description ");
        $triggerId = uniqid('my-php-test-trigger-');
        $fullTriggerId = sprintf('projects/%s/jobTriggers/%s', self::$projectId, $triggerId);

        $output = $this->runCommand('create-trigger', [
            'bucket-name' => $bucketName,
            'display-name' => $displayName,
            'description' => $description,
            'trigger-id' => $triggerId,
            'frequency' => 1,
            'auto-populate-timespan' => true,
        ]);
        $this->assertContains('Successfully created trigger ' . $triggerId, $output);

        $output = $this->runCommand('list-triggers', []);
        $this->assertContains('Trigger ' . $fullTriggerId, $output);
        $this->assertContains('Display Name: ' . $displayName, $output);
        $this->assertContains('Description: ' . $description, $output);
        $this->assertContains('Auto-populates timespan config: yes', $output);

        $output = $this->runCommand('delete-trigger', [
            'trigger-id' => $fullTriggerId
        ]);
        $this->assertContains('Successfully deleted trigger ' . $fullTriggerId, $output);
    }

    public function testInspectTemplates()
    {
        $displayName = uniqid("My inspect template display name ");
        $description = uniqid("My inspect template description ");
        $templateId = uniqid('my-php-test-inspect-template-');
        $fullTemplateId = sprintf('projects/%s/inspectTemplates/%s', self::$projectId, $templateId);

        $output  = $this->runCommand('create-inspect-template', [
            'template-id' => $templateId,
            'display-name' => $displayName,
            'description' => $description
        ]);
        $this->assertContains('Successfully created template ' . $fullTemplateId, $output);

        $output = $this->runCommand('list-inspect-templates', []);
        $this->assertContains('Template ' . $fullTemplateId, $output);
        $this->assertContains('Display Name: ' . $displayName, $output);
        $this->assertContains('Description: ' . $description, $output);

        $output = $this->runCommand('delete-inspect-template', [
            'template-id' => $fullTemplateId
        ]);
        $this->assertContains('Successfully deleted template ' . $fullTemplateId, $output);
    }

    public function testNumericalStats()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('numerical-stats', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'column-name' => 'Age'
        ]);

        $this->assertRegExp('/Value range: \[\d+, \d+\]/', $output);
        $this->assertRegExp('/Value at \d+ quantile: \d+/', $output);
    }

    public function testCategoricalStats()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('categorical-stats', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'column-name' => 'Gender'
        ]);

        $this->assertRegExp('/Most common value occurs \d+ time\(s\)/', $output);
        $this->assertRegExp('/Least common value occurs \d+ time\(s\)/', $output);
        $this->assertRegExp('/\d+ unique value\(s\) total/', $output);
    }

    public function testKAnonymity()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('k-anonymity', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'quasi-ids' => 'Age,Gender'
        ]);
        $this->assertRegExp('/Quasi-ID values: \{\d{2}, Female\}/', $output);
        $this->assertRegExp('/Class size: \d/', $output);
    }

    public function testLDiversity()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('l-diversity', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'quasi-ids' => 'Age,Gender',
            'sensitive-attribute' => 'Name'
        ]);
        $this->assertRegExp('/Quasi-ID values: \{\d{2}, Female\}/', $output);
        $this->assertRegExp('/Class size: \d/', $output);
        $this->assertRegExp('/Sensitive value James occurs \d time\(s\)/', $output);
    }

    public function testKMap()
    {
        $topicId = $this->requireEnv('DLP_TOPIC');
        $subId = $this->requireEnv('DLP_SUBSCRIPTION');

        $output = $this->runCommand('k-map', [
            'dataset' => 'integration_tests_dlp',
            'table' => 'harmful',
            'calling-project' => self::$projectId,
            'data-project' => self::$projectId,
            'topic-id' => $topicId,
            'subscription-id' => $subId,
            'region-code' => 'US',
            'quasi-ids' => 'Age,Gender',
            'info-types' => 'AGE,GENDER'
        ]);
        $this->assertRegExp('/Anonymity range: \[\d, \d\]/', $output);
        $this->assertRegExp('/Size: \d/', $output);
        $this->assertRegExp('/Values: \{\d{2}, Female\}/', $output);
    }

    public function testJobs()
    {
        $jobIdRegex = "~projects/.*/dlpJobs/i-\d+~";

        $output = $this->runCommand('list-jobs', [
            'filter' => 'state=DONE'
        ]);

        $this->assertRegExp($jobIdRegex, $output);
        preg_match($jobIdRegex, $output, $jobIds);
        $jobId = $jobIds[0];

        $output = $this->runCommand('delete-job', [
            'job-id' => $jobId
        ]);
        $this->assertContains('Successfully deleted job ' . $jobId, $output);
    }
}
