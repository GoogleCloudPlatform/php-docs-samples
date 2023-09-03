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

use Google\Cloud\Dlp\V2\DlpJob;
use Google\Cloud\Dlp\V2\DlpJob\JobState;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnitRetry\RetryTrait;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeStats;
use Google\Cloud\Dlp\V2\InspectDataSourceDetails;
use Google\Cloud\Dlp\V2\InspectDataSourceDetails\Result;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult\KAnonymityEquivalenceClass;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult\KAnonymityHistogramBucket;
use Google\Cloud\Dlp\V2\Value;
use Google\Cloud\Dlp\V2\HybridInspectResponse;
use Google\Cloud\Dlp\V2\HybridOptions;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\JobTrigger;
use Google\Cloud\Dlp\V2\JobTrigger\Status;
use Google\Cloud\Dlp\V2\JobTrigger\Trigger;
use Google\Cloud\Dlp\V2\Manual;
use Google\Cloud\Dlp\V2\StorageConfig;

/**
 * Unit Tests for dlp commands.
 */
class dlpTest extends TestCase
{
    use TestTrait;
    use RetryTrait;
    use ProphecyTrait;
    private static $topic;
    private static $subscription;

    public static function setUpBeforeClass(): void
    {
        $uniqueName = sprintf('dlp-%s', microtime(true));
        $pubsub = new PubSubClient();
        self::$topic = $pubsub->topic($uniqueName);
        self::$topic->create();
        self::$subscription = self::$topic->subscription($uniqueName);
        self::$subscription->create();
    }

    public static function tearDownAfterClass(): void
    {
        self::$topic->delete();
        self::$subscription->delete();
    }

    private function writeTempSample(string $sampleName, array $replacements): string
    {
        $sampleFile = sprintf('%s/../src/%s.php', __DIR__, $sampleName);
        $tmpFileName = 'dlp_' . basename($sampleFile, '.php');
        $tmpFilePath = sys_get_temp_dir() . '/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements[$sampleName] = $tmpFileName;
        $fileContent = strtr($fileContent, $replacements);

        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent
        );

        return $tmpFilePath;
    }

    public function dlpJobResponse()
    {
        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/i-3208317104051988812')
            ->setState(JobState::PENDING);

        $result = $this->prophesize(Result::class);
        $infoTypeStats1 = $this->prophesize(InfoTypeStats::class);
        $infoTypeStats1->getInfoType()->shouldBeCalled()->willReturn((new InfoType())->setName('PERSON_NAME'));
        $infoTypeStats1->getCount()->shouldBeCalled()->willReturn(5);
        $result->getInfoTypeStats()->shouldBeCalled()->willReturn([$infoTypeStats1->reveal()]);

        $inspectDetails = $this->prophesize(InspectDataSourceDetails::class);
        $inspectDetails->getResult()->shouldBeCalled()->willReturn($result->reveal());

        $getDlpJobResponse = $this->prophesize(DlpJob::class);
        $getDlpJobResponse->getName()->shouldBeCalled()->willReturn('projects/' . self::$projectId . '/dlpJobs/i-3208317104051988812');
        $getDlpJobResponse->getState()->shouldBeCalled()->willReturn(JobState::DONE);
        $getDlpJobResponse->getInspectDetails()->shouldBeCalled()->willReturn($inspectDetails->reveal());

        return ['createDlpJob' => $createDlpJobResponse, 'getDlpJob' => $getDlpJobResponse];
    }

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
        $this->assertMatchesRegularExpression('/My SSN is SSN_TOKEN\(9\):\d+/', $deidOutput);

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

        $this->assertMatchesRegularExpression($jobIdRegex, $output);
        preg_match($jobIdRegex, $output, $jobIds);
        $jobId = $jobIds[0];

        $output = $this->runFunctionSnippet(
            'delete_job',
            [$jobId]
        );
        $this->assertStringContainsString('Successfully deleted job ' . $jobId, $output);
    }

    public function testInspectHotwordRules()
    {
        $output = $this->runFunctionSnippet('inspect_hotword_rule', [
            self::$projectId,
            "Patient's MRN 444-5-22222 and just a number 333-2-33333"
        ]);
        $this->assertStringContainsString('Info type: C_MRN', $output);
    }

    public function testDeidentifyRedact()
    {
        $output = $this->runFunctionSnippet('deidentify_redact', [
            self::$projectId,
            'My name is Alicia Abernathy, and my email address is aabernathy@example.com'
        ]);
        $this->assertStringNotContainsString('aabernathy@example.com', $output);
    }

    public function testInspectCustomRegex()
    {
        $output = $this->runFunctionSnippet('inspect_custom_regex', [
            self::$projectId,
            'Patients MRN 444-5-22222'
        ]);
        $this->assertStringContainsString('Info type: C_MRN', $output);
    }

    public function testInspectStringOmitOverlap()
    {
        $output = $this->runFunctionSnippet('inspect_string_omit_overlap', [
            self::$projectId,
            'james@example.org is an email.'
        ]);
        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectStringCustomOmitOverlap()
    {
        $output = $this->runFunctionSnippet('inspect_string_custom_omit_overlap', [
            self::$projectId,
            'Name: Jane Doe. Name: Larry Page.'
        ]);

        $this->assertStringContainsString('Info type: PERSON_NAME', $output);
        $this->assertStringContainsString('Jane Doe', $output);
        $this->assertStringNotContainsString('Larry Page', $output);
    }

    public function testInspectPhoneNumber()
    {
        $output = $this->runFunctionSnippet('inspect_phone_number', [
            self::$projectId,
            'My name is Gary and my phone number is (415) 555-0890'
        ]);
        $this->assertStringContainsString('Info type: PHONE_NUMBER', $output);
    }

    public function testDeIdentifyExceptionList()
    {
        $output = $this->runFunctionSnippet('deidentify_exception_list', [
            self::$projectId,
            'jack@example.org accessed customer record of user5@example.com'
        ]);
        $this->assertStringContainsString('[EMAIL_ADDRESS]', $output);
        $this->assertStringContainsString('jack@example.org', $output);
        $this->assertStringNotContainsString('user5@example.com', $output);
    }

    public function testDeidentifySimpleWordList()
    {
        $output = $this->runFunctionSnippet('deidentify_simple_word_list', [
            self::$projectId,
            'Patient was seen in RM-YELLOW then transferred to rm green.'
        ]);
        $this->assertStringContainsString('[CUSTOM_ROOM_ID]', $output);
    }

    public function testInspectStringWithoutOverlap()
    {
        $output = $this->runFunctionSnippet('inspect_string_without_overlap', [
            self::$projectId,
            'example.com is a domain, james@example.org is an email.'
        ]);

        $this->assertStringContainsString('Info type: DOMAIN_NAME', $output);
        $this->assertStringNotContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectStringWithExclusionDict()
    {
        $output = $this->runFunctionSnippet('inspect_string_with_exclusion_dict', [
            self::$projectId,
            'Some email addresses: gary@example.com, example@example.com'
        ]);

        $this->assertStringContainsString('Quote: gary@example.com', $output);
        $this->assertStringNotContainsString('Quote: example@example.com', $output);
    }

    public function testInspectStringWithExclusionDictSubstring()
    {
        $excludedSubStringArray = ['Test'];
        $output = $this->runFunctionSnippet('inspect_string_with_exclusion_dict_substring', [
            self::$projectId,
            'Some email addresses: gary@example.com, TEST@example.com',
            $excludedSubStringArray
        ]);
        $this->assertStringContainsString('Quote: gary@example.com', $output);
        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
        $this->assertStringContainsString('Quote: example.com', $output);
        $this->assertStringContainsString('Info type: DOMAIN_NAME', $output);
        $this->assertStringNotContainsString('TEST@example.com', $output);
    }

    public function testInspectStringMultipleRulesPatientRule()
    {
        $output = $this->runFunctionSnippet('inspect_string_multiple_rules', [
            self::$projectId,
            'patient: Jane Doe'
        ]);

        $this->assertStringContainsString('Info type: PERSON_NAME', $output);
    }

    public function testInspectStringMultipleRulesDoctorRule()
    {
        $output = $this->runFunctionSnippet('inspect_string_multiple_rules', [
            self::$projectId,
            'doctor: Jane Doe'
        ]);

        $this->assertStringContainsString('No findings.', $output);
    }

    public function testInspectStringMultipleRulesQuasimodoRule()
    {
        $output = $this->runFunctionSnippet('inspect_string_multiple_rules', [
            self::$projectId,
            'patient: Quasimodo'
        ]);

        $this->assertStringContainsString('No findings.', $output);
    }

    public function testInspectStringMultipleRulesRedactedRule()
    {
        $output = $this->runFunctionSnippet('inspect_string_multiple_rules', [
            self::$projectId,
            'name of patient: REDACTED'
        ]);

        $this->assertStringContainsString('No findings.', $output);
    }

    public function testInspectStringCustomHotword()
    {
        $output = $this->runFunctionSnippet('inspect_string_custom_hotword', [
            self::$projectId,
            'patient name: John Doe'
        ]);
        $this->assertStringContainsString('Info type: PERSON_NAME', $output);
        $this->assertStringContainsString('Likelihood: VERY_LIKELY', $output);
    }

    public function testInspectStringWithExclusionRegex()
    {
        $output = $this->runFunctionSnippet('inspect_string_with_exclusion_regex', [
            self::$projectId,
            'Some email addresses: gary@example.com, bob@example.org'
        ]);

        $this->assertStringContainsString('Quote: bob@example.org', $output);
        $this->assertStringNotContainsString('gray@example.com', $output);
    }

    public function testInspectStringCustomExcludingSubstring()
    {
        $output = $this->runFunctionSnippet('inspect_string_custom_excluding_substring', [
            self::$projectId,
            'Name: Doe, John. Name: Example, Jimmy'
        ]);

        $this->assertStringContainsString('Info type: CUSTOM_NAME_DETECTOR', $output);
        $this->assertStringContainsString('Doe', $output);
        $this->assertStringContainsString('John', $output);
        $this->assertStringNotContainsString('Jimmy', $output);
        $this->assertStringNotContainsString('Example', $output);
    }

    public function testDeidentifyReplace()
    {
        $string = 'My name is Alicia Abernathy, and my email address is aabernathy@example.com.';
        $output = $this->runFunctionSnippet('deidentify_replace', [
            self::$projectId,
            $string
        ]);
        $this->assertStringContainsString('[email-address]', $output);
        $this->assertNotEquals($output, $string);
    }

    public function testDeidentifyTableInfotypes()
    {
        $inputCsvFile = __DIR__ . '/data/table1.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_infotypes_output_unitest.csv';
        $output = $this->runFunctionSnippet('deidentify_table_infotypes', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile,
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString('[PERSON_NAME]', $csvLines_ouput[1]);
        $this->assertStringNotContainsString('Charles Dickens', $csvLines_ouput[1]);

        unlink($outputCsvFile);
    }

    public function testDeidentifyTableConditionMasking()
    {
        $inputCsvFile = __DIR__ . '/data/table2.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_condition_masking_output_unittest.csv';

        $output = $this->runFunctionSnippet('deidentify_table_condition_masking', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile
        ]);
        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString('**', $csvLines_ouput[1]);

        unlink($outputCsvFile);
    }

    public function testDeidentifyTableConditionInfotypes()
    {
        $inputCsvFile = __DIR__ . '/data/table1.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_condition_infotypes_output_unittest.csv';

        $output = $this->runFunctionSnippet('deidentify_table_condition_infotypes', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile
        ]);

        $this->assertNotEquals(
            sha1_file($inputCsvFile),
            sha1_file($outputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString('[PERSON_NAME]', $csvLines_ouput[1]);
        $this->assertStringNotContainsString('Charles Dickens', $csvLines_ouput[1]);
        $this->assertStringNotContainsString('[PERSON_NAME]', $csvLines_ouput[2]);
        $this->assertStringContainsString('Jane Austen', $csvLines_ouput[2]);

        unlink($outputCsvFile);
    }

    public function testDeidentifyTableBucketing()
    {
        $inputCsvFile = __DIR__ . '/data/table2.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_bucketing_output_unittest.csv';

        $output = $this->runFunctionSnippet('deidentify_table_bucketing', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString('90:100', $csvLines_ouput[1]);
        $this->assertStringContainsString('20:30', $csvLines_ouput[2]);
        $this->assertStringContainsString('70:80', $csvLines_ouput[3]);

        unlink($outputCsvFile);
    }

    public function testDeidentifyTableRowSuppress()
    {
        $inputCsvFile = __DIR__ . '/data/table2.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_row_suppress_output_unitest.csv';
        $output = $this->runFunctionSnippet('deidentify_table_row_suppress', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile,
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertEquals(3, count($csvLines_ouput));
        unlink($outputCsvFile);
    }

    public function testInspectImageAllInfoTypes()
    {
        $output = $this->runFunctionSnippet('inspect_image_all_infotypes', [
            self::$projectId,
            __DIR__ . '/data/test.png'
        ]);
        $this->assertStringContainsString('Info type: PHONE_NUMBER', $output);
        $this->assertStringContainsString('Info type: PERSON_NAME', $output);
        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
    }

    public function testInspectImageListedInfotypes()
    {
        $output = $this->runFunctionSnippet('inspect_image_listed_infotypes', [
            self::$projectId,
            __DIR__ . '/data/test.png'
        ]);

        $this->assertStringContainsString('Info type: EMAIL_ADDRESS', $output);
        $this->assertStringContainsString('Info type: PHONE_NUMBER', $output);
    }

    public function testInspectAugmentInfotypes()
    {
        $textToInspect = "The patient's name is Quasimodo";
        $matchWordList = ['quasimodo'];
        $output = $this->runFunctionSnippet('inspect_augment_infotypes', [
            self::$projectId,
            $textToInspect,
            $matchWordList
        ]);
        $this->assertStringContainsString('Quote: Quasimodo', $output);
        $this->assertStringContainsString('Info type: PERSON_NAME', $output);
    }

    public function testInspectAugmentInfotypesIgnore()
    {
        $textToInspect = 'My mobile number is 9545141023';
        $matchWordList = ['quasimodo'];
        $output = $this->runFunctionSnippet('inspect_augment_infotypes', [
            self::$projectId,
            $textToInspect,
            $matchWordList
        ]);
        $this->assertStringContainsString('No findings.', $output);
    }

    public function testInspectColumnValuesWCustomHotwords()
    {
        $output = $this->runFunctionSnippet('inspect_column_values_w_custom_hotwords', [
            self::$projectId,
        ]);
        $this->assertStringContainsString('Info type: US_SOCIAL_SECURITY_NUMBER', $output);
        $this->assertStringContainsString('Likelihood: VERY_LIKELY', $output);
        $this->assertStringContainsString('Quote: 222-22-2222', $output);
        $this->assertStringNotContainsString('111-11-1111', $output);
    }

    public function testInspectTable()
    {
        $output = $this->runFunctionSnippet('inspect_table', [
            self::$projectId
        ]);

        $this->assertStringContainsString('Info type: PHONE_NUMBER', $output);
        $this->assertStringContainsString('Quote: (206) 555-0123', $output);
        $this->assertStringNotContainsString('Info type: PERSON_NAME', $output);
    }

    public function testDeidReidFPEUsingSurrogate()
    {
        $unwrappedKey = 'YWJjZGVmZ2hpamtsbW5vcA==';
        $string = 'My PHONE NUMBER IS 7319976811';
        $surrogateTypeName = 'PHONE_TOKEN';

        $deidOutput = $this->runFunctionSnippet('deidentify_free_text_with_fpe_using_surrogate', [
            self::$projectId,
            $string,
            $unwrappedKey,
            $surrogateTypeName,
        ]);
        $this->assertMatchesRegularExpression('/My PHONE NUMBER IS PHONE_TOKEN\(\d+\):\d+/', $deidOutput);

        $reidOutput = $this->runFunctionSnippet('reidentify_free_text_with_fpe_using_surrogate', [
            self::$projectId,
            $deidOutput,
            $unwrappedKey,
            $surrogateTypeName,
        ]);
        $this->assertEquals($string, $reidOutput);
    }

    public function testDeIdentifyTableFpe()
    {
        $inputCsvFile = __DIR__ . '/data/fpe_input.csv';
        $outputCsvFile = __DIR__ . '/data/fpe_output_unittest.csv';
        $outputCsvFile2 = __DIR__ . '/data/reidentify_fpe_ouput_unittest.csv';
        $encryptedFieldNames = 'EmployeeID';
        $keyName = $this->requireEnv('DLP_DEID_KEY_NAME');
        $wrappedKey = $this->requireEnv('DLP_DEID_WRAPPED_KEY');

        $output = $this->runFunctionSnippet('deidentify_table_fpe', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile,
            $encryptedFieldNames,
            $keyName,
            $wrappedKey,
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $output = $this->runFunctionSnippet('reidentify_table_fpe', [
            self::$projectId,
            $outputCsvFile,
            $outputCsvFile2,
            $encryptedFieldNames,
            $keyName,
            $wrappedKey,
        ]);

        $this->assertEquals(
            sha1_file($inputCsvFile),
            sha1_file($outputCsvFile2)
        );
        unlink($outputCsvFile);
        unlink($outputCsvFile2);
    }

    public function testDeidReidDeterministic()
    {
        $inputString = 'My PHONE NUMBER IS 731997681';
        $infoTypeName = 'PHONE_NUMBER';
        $surrogateTypeName = 'PHONE_TOKEN';
        $keyName = $this->requireEnv('DLP_DEID_KEY_NAME');
        $wrappedKey = $this->requireEnv('DLP_DEID_WRAPPED_KEY');

        $deidOutput = $this->runFunctionSnippet('deidentify_deterministic', [
            self::$projectId,
            $keyName,
            $wrappedKey,
            $inputString,
            $infoTypeName,
            $surrogateTypeName
        ]);
        $this->assertMatchesRegularExpression('/My PHONE NUMBER IS PHONE_TOKEN\(\d+\):\(\w|\/|=|\)+/', $deidOutput);

        $reidOutput = $this->runFunctionSnippet('reidentify_deterministic', [
            self::$projectId,
            $deidOutput,
            $surrogateTypeName,
            $keyName,
            $wrappedKey,
        ]);
        $this->assertEquals($inputString, $reidOutput);
    }

    public function testDeidReidTextFPE()
    {
        $string = 'My SSN is 372819127';
        $keyName = $this->requireEnv('DLP_DEID_KEY_NAME');
        $wrappedKey = $this->requireEnv('DLP_DEID_WRAPPED_KEY');
        $surrogateType = 'SSN_TOKEN';

        $deidOutput = $this->runFunctionSnippet('deidentify_fpe', [
            self::$projectId,
            $string,
            $keyName,
            $wrappedKey,
            $surrogateType,
        ]);
        $this->assertMatchesRegularExpression('/My SSN is SSN_TOKEN\(\d+\):\d+/', $deidOutput);

        $reidOutput = $this->runFunctionSnippet('reidentify_text_fpe', [
            self::$projectId,
            $deidOutput,
            $keyName,
            $wrappedKey,
            $surrogateType,
        ]);
        $this->assertEquals($string, $reidOutput);
    }

    public function testGetJob()
    {

        // Set filter to only go back a day, so that we do not pull every job.
        $filter = sprintf(
            'state=DONE AND end_time>"%sT00:00:00+00:00"',
            date('Y-m-d', strtotime('-1 day'))
        );
        $jobIdRegex = "~projects/.*/dlpJobs/i-\d+~";
        $getJobName = $this->runFunctionSnippet('list_jobs', [
            self::$projectId,
            $filter,
        ]);
        preg_match($jobIdRegex, $getJobName, $jobIds);
        $jobName = $jobIds[0];

        $output = $this->runFunctionSnippet('get_job', [
            $jobName
        ]);
        $this->assertStringContainsString('Job ' . $jobName . ' status:', $output);
    }

    public function testCreateJob()
    {
        $gcsPath = $this->requireEnv('GCS_PATH');
        $jobIdRegex = "~projects/.*/dlpJobs/i-\d+~";
        $jobName = $this->runFunctionSnippet('create_job', [
            self::$projectId,
            $gcsPath
        ]);
        $this->assertRegExp($jobIdRegex, $jobName);
        $output = $this->runFunctionSnippet(
            'delete_job',
            [$jobName]
        );
        $this->assertStringContainsString('Successfully deleted job ' . $jobName, $output);
    }

    public function testRedactImageListedInfotypes()
    {
        $imagePath = __DIR__ . '/data/test.png';
        $outputPath = __DIR__ . '/data/redact_image_listed_infotypes-unittest.png';

        $output = $this->runFunctionSnippet('redact_image_listed_infotypes', [
            self::$projectId,
            $imagePath,
            $outputPath,
        ]);
        $this->assertNotEquals(
            sha1_file($outputPath),
            sha1_file($imagePath)
        );
        unlink($outputPath);
    }

    public function testRedactImageAllText()
    {
        $imagePath = __DIR__ . '/data/test.png';
        $outputPath = __DIR__ . '/data/redact_image_all_text-unittest.png';

        $output = $this->runFunctionSnippet('redact_image_all_text', [
            self::$projectId,
            $imagePath,
            $outputPath,
        ]);
        $this->assertNotEquals(
            sha1_file($outputPath),
            sha1_file($imagePath)
        );
        unlink($outputPath);
    }

    public function testRedactImageAllInfoTypes()
    {
        $imagePath = __DIR__ . '/data/test.png';
        $outputPath = __DIR__ . '/data/redact_image_all_infotypes-unittest.png';

        $output = $this->runFunctionSnippet('redact_image_all_infotypes', [
            self::$projectId,
            $imagePath,
            $outputPath,
        ]);
        $this->assertNotEquals(
            sha1_file($outputPath),
            sha1_file($imagePath)
        );
        unlink($outputPath);
    }

    public function testRedactImageColoredInfotypes()
    {
        $imagePath = __DIR__ . '/data/test.png';
        $outputPath = __DIR__ . '/data/sensitive-data-image-redacted-color-coding-unittest.png';

        $output = $this->runFunctionSnippet('redact_image_colored_infotypes', [
            self::$projectId,
            $imagePath,
            $outputPath,
        ]);
        $this->assertNotEquals(
            sha1_file($outputPath),
            sha1_file($imagePath)
        );
        unlink($outputPath);
    }

    public function testDeidentifyTimeExtract()
    {
        $inputCsvFile = __DIR__ . '/data/table3.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_time_extract_output_unittest.csv';

        $output = $this->runFunctionSnippet('deidentify_time_extract', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString(',1970', $csvLines_ouput[1]);

        unlink($outputCsvFile);
    }

    public function testDeidentifyDictionaryReplacement()
    {
        $string = 'My name is Charlie and email address is charlie@example.com.';
        $output = $this->runFunctionSnippet('deidentify_dictionary_replacement', [
            self::$projectId,
            $string
        ]);
        $this->assertStringNotContainsString('charlie@example.com', $output);
        $this->assertNotEquals($output, $string);
    }

    public function testDeidentifyTablePrimitiveBucketing()
    {
        $inputCsvFile = __DIR__ . '/data/table4.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_primitive_bucketing_output_unittest.csv';

        $output = $this->runFunctionSnippet('deidentify_table_primitive_bucketing', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringContainsString('High', $csvLines_ouput[1]);
        unlink($outputCsvFile);
    }

    public function testDeidentifyTableWithCryptoHash()
    {
        $inputCsvFile = __DIR__ . '/data/table5.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_with_crypto_hash_output_unittest.csv';
        // Generate randome string.
        $transientCryptoKeyName = sha1(rand());

        $output = $this->runFunctionSnippet('deidentify_table_with_crypto_hash', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile,
            $transientCryptoKeyName
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringNotContainsString('user1@example.org', $csvLines_ouput[1]);
        unlink($outputCsvFile);
    }

    public function testDeidentifyTableWithMultipleCryptoHash()
    {
        $inputCsvFile = __DIR__ . '/data/table6.csv';
        $outputCsvFile = __DIR__ . '/data/deidentify_table_with_multiple_crypto_hash_output_unittest.csv';
        // Generate randome string.
        $transientCryptoKeyName1 = sha1(rand());
        $transientCryptoKeyName2 = sha1(rand());

        $output = $this->runFunctionSnippet('deidentify_table_with_multiple_crypto_hash', [
            self::$projectId,
            $inputCsvFile,
            $outputCsvFile,
            $transientCryptoKeyName1,
            $transientCryptoKeyName2
        ]);

        $this->assertNotEquals(
            sha1_file($outputCsvFile),
            sha1_file($inputCsvFile)
        );

        $csvLines_input = file($inputCsvFile, FILE_IGNORE_NEW_LINES);
        $csvLines_ouput = file($outputCsvFile, FILE_IGNORE_NEW_LINES);

        $this->assertEquals($csvLines_input[0], $csvLines_ouput[0]);
        $this->assertStringNotContainsString('user1@example.org', $csvLines_ouput[1]);
        $this->assertStringContainsString('abbyabernathy1', $csvLines_ouput[2]);
        unlink($outputCsvFile);
    }

    public function testDeidentifyCloudStorage()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $inputgcsPath = 'gs://' . $bucketName;
        $outgcsPath = 'gs://' . $bucketName;
        $deidentifyTemplateName = $this->requireEnv('DLP_DEIDENTIFY_TEMPLATE');
        $structuredDeidentifyTemplateName = $this->requireEnv('DLP_STRUCTURED_DEIDENTIFY_TEMPLATE');
        $imageRedactTemplateName = $this->requireEnv('DLP_IMAGE_REDACT_DEIDENTIFY_TEMPLATE');
        $datasetId = $this->requireEnv('DLP_DATASET_ID');
        $tableId = $this->requireEnv('DLP_TABLE_ID');

        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_deidentify_cloud_storage('%s','%s','%s','%s','%s','%s','%s','%s');",
            self::$projectId,
            $inputgcsPath,
            $outgcsPath,
            $deidentifyTemplateName,
            $structuredDeidentifyTemplateName,
            $imageRedactTemplateName,
            $datasetId,
            $tableId
        );

        $tmpFile = $this->writeTempSample('deidentify_cloud_storage', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('projects/' . self::$projectId . '/dlpJobs', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }

    public function testDeidentifyReplaceInfotype()
    {
        $inputString = 'Please call Steve Smith. His number is (555) 253-0000.';
        $output = $this->runFunctionSnippet('deidentify_replace_infotype', [
            self::$projectId,
            $inputString
        ]);
        $this->assertStringContainsString('[PHONE_NUMBER]', $output);
        $this->assertStringContainsString('[PERSON_NAME]', $output);
    }

    public function testKAnonymityWithEntityId()
    {
        $datasetId = $this->requireEnv('DLP_DATASET_ID');
        $tableId = $this->requireEnv('DLP_TABLE_ID');

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setRiskDetails((new AnalyzeDataSourceRiskDetails())
                    ->setKAnonymityResult((new KAnonymityResult())
                            ->setEquivalenceClassHistogramBuckets([(new KAnonymityHistogramBucket())
                                ->setEquivalenceClassSizeLowerBound(1)
                                ->setEquivalenceClassSizeUpperBound(1)
                                ->setBucketValues([
                                    (new KAnonymityEquivalenceClass())
                                        ->setQuasiIdsValues([(new Value())
                                            ->setStringValue('{"stringValue":"[\"19\",\"8291 3627 8250 1234\"]"}')])
                                        ->setEquivalenceClassSize(1),
                                    (new KAnonymityEquivalenceClass())
                                        ->setQuasiIdsValues([(new Value())
                                            ->setStringValue('{"stringValue":"[\"27\",\"4231 5555 6781 9876\"]"}')])
                                        ->setEquivalenceClassSize(1)

                                ])])
                    )
            );

        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($createDlpJobResponse);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($getDlpJobResponse);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_k_anonymity_with_entity_id('%s','%s','%s',%s);",
            self::$projectId,
            $datasetId,
            $tableId,
            "['Age', 'Mystery']"
        );

        $tmpFile = $this->writeTempSample('k_anonymity_with_entity_id', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('Bucket size range: [1, 1]', $output);
    }

    public function create_hybrid_job_trigger(
        string $triggerId,
        string $displayName,
        string $description
    ): string {
        // Instantiate a client.
        $dlp = new DlpServiceClient();

        // Create the inspectConfig object.
        $inspectConfig = (new InspectConfig())
            ->setInfoTypes([
                (new InfoType())
                    ->setName('PERSON_NAME'),
                (new InfoType())
                    ->setName('PHONE_NUMBER')
            ])
            ->setIncludeQuote(true);

        $storageConfig = (new StorageConfig())
            ->setHybridOptions((new HybridOptions())
                ->setRequiredFindingLabelKeys(
                    ['appointment-bookings-comments']
                )
                ->setLabels([
                    'env' => 'prod'
                ]));

        // Construct the insJobConfig object.
        $inspectJobConfig = (new InspectJobConfig())
            ->setInspectConfig($inspectConfig)
            ->setStorageConfig($storageConfig);

        // Create triggers
        $triggerObject = (new Trigger())
            ->setManual(new Manual());

        // ----- Construct trigger object -----
        $jobTriggerObject = (new JobTrigger())
            ->setTriggers([$triggerObject])
            ->setInspectJob($inspectJobConfig)
            ->setStatus(Status::HEALTHY)
            ->setDisplayName($displayName)
            ->setDescription($description);

        // Run trigger creation request
        $parent = 'projects/' . self::$projectId . '/locations/global';
        $trigger = $dlp->createJobTrigger($parent, $jobTriggerObject, [
            'triggerId' => $triggerId
        ]);

        return $trigger->getName();
    }

    public function testInspectSendDataToHybridJobTrigger()
    {
        $displayName = uniqid('My trigger display name ');
        $description = uniqid('My trigger description ');
        $triggerId = uniqid('my-php-test-trigger-');
        $string = 'My email is test@example.org';

        $fullTriggerId = $this->create_hybrid_job_trigger(
            $triggerId,
            $displayName,
            $description
        );

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);
        $dlpJobResponse = $this->dlpJobResponse();

        $dlpServiceClientMock
            ->activateJobTrigger($fullTriggerId)
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        $dlpServiceClientMock
            ->listDlpJobs(Argument::any(), Argument::type('array'))
            ->willReturn([$dlpJobResponse['getDlpJob']]);

        $dlpServiceClientMock
            ->hybridInspectJobTrigger(Argument::any(), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->willReturn(new HybridInspectResponse());

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_send_data_to_hybrid_job_trigger('%s','%s','%s');",
            self::$projectId,
            $triggerId,
            $string
        );

        $tmpFile = $this->writeTempSample('inspect_send_data_to_hybrid_job_trigger', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('projects/' . self::$projectId . '/dlpJobs', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);

        $output = $this->runFunctionSnippet('delete_trigger', [
            self::$projectId,
            $triggerId
        ]);
        $this->assertStringContainsString('Successfully deleted trigger ' . $fullTriggerId, $output);
    }

    public function testInspectBigQueryWithSampling()
    {
        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        $topicId = self::$topic->name();
        $subscriptionId = self::$subscription->name();

        $pubSubClientMock = $this->prophesize(PubSubClient::class);
        $topicMock = $this->prophesize(Topic::class);
        $subscriptionMock = $this->prophesize(Subscription::class);
        $messageMock = $this->prophesize(Message::class);

        // Set up the mock expectations for the Pub/Sub functions
        $pubSubClientMock->topic($topicId)
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . $topicId);

        $topicMock->subscription($subscriptionId)
            ->shouldBeCalled()
            ->willReturn($subscriptionMock->reveal());

        $subscriptionMock->pull()
            ->shouldBeCalled()
            ->willReturn([$messageMock->reveal()]);

        $messageMock->attributes()
            ->shouldBeCalledTimes(2)
            ->willReturn(['DlpJobName' => 'projects/' . self::$projectId . '/dlpJobs/i-3208317104051988812']);

        $subscriptionMock->acknowledge(Argument::any())
            ->shouldBeCalled()
            ->willReturn($messageMock->reveal());

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_bigquery_with_sampling('%s','%s','%s','%s','%s','%s');",
            self::$projectId,
            $topicId,
            $subscriptionId,
            'bigquery-public-data',
            'usa_names',
            'usa_1910_current'
        );

        $tmpFile = $this->writeTempSample('inspect_bigquery_with_sampling', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }

    public function testInspectGcsWithSampling()
    {
        $gcsUri = $this->requireEnv('GCS_PATH');

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        $topicId = self::$topic->name();
        $subscriptionId = self::$subscription->name();

        $pubSubClientMock = $this->prophesize(PubSubClient::class);
        $topicMock = $this->prophesize(Topic::class);
        $subscriptionMock = $this->prophesize(Subscription::class);
        $messageMock = $this->prophesize(Message::class);

        // Set up the mock expectations for the Pub/Sub functions
        $pubSubClientMock->topic($topicId)
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . $topicId);

        $topicMock->subscription($subscriptionId)
            ->shouldBeCalled()
            ->willReturn($subscriptionMock->reveal());

        $subscriptionMock->pull()
            ->shouldBeCalled()
            ->willReturn([$messageMock->reveal()]);

        $messageMock->attributes()
            ->shouldBeCalledTimes(2)
            ->willReturn(['DlpJobName' => 'projects/' . self::$projectId . '/dlpJobs/i-3208317104051988812']);

        $subscriptionMock->acknowledge(Argument::any())
            ->shouldBeCalled()
            ->willReturn($messageMock->reveal());

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_gcs_with_sampling('%s','%s','%s','%s');",
            self::$projectId,
            $gcsUri,
            $topicId,
            $subscriptionId
        );

        $tmpFile = $this->writeTempSample('inspect_gcs_with_sampling', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }

    public function testInspectGcsSendToScc()
    {
        $gcsPath = $this->requireEnv('GCS_PATH');

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_gcs_send_to_scc('%s','%s');",
            self::$projectId,
            $gcsPath
        );

        $tmpFile = $this->writeTempSample('inspect_gcs_send_to_scc', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('projects/' . self::$projectId . '/dlpJobs', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }

    public function testInspectDatastoreSendToScc()
    {
        $datastorename = $this->requireEnv('DLP_DATASTORE_KIND');
        $namespaceId = $this->requireEnv('DLP_NAMESPACE_ID');

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_datastore_send_to_scc('%s','%s','%s');",
            self::$projectId,
            $datastorename,
            $namespaceId
        );

        $tmpFile = $this->writeTempSample('inspect_datastore_send_to_scc', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);
        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('projects/' . self::$projectId . '/dlpJobs', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }

    public function testInspectBigquerySendToScc()
    {
        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

        // Creating a temp file for testing.
        $callFunction = sprintf(
            "dlp_inspect_bigquery_send_to_scc('%s','%s','%s','%s');",
            self::$projectId,
            'bigquery-public-data',
            'usa_names',
            'usa_1910_current'
        );

        $tmpFile = $this->writeTempSample('inspect_bigquery_send_to_scc', [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            "require_once __DIR__ . '/../../testing/sample_helpers.php';" => '',
            '\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);' => $callFunction
        ]);

        global $dlp;

        $dlp = $dlpServiceClientMock->reveal();

        // Invoke file and capture output
        ob_start();
        include $tmpFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('projects/' . self::$projectId . '/dlpJobs', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
    }
}
