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
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeStats;
use Google\Cloud\Dlp\V2\InspectDataSourceDetails;
use Google\Cloud\Dlp\V2\InspectDataSourceDetails\Result;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

/**
 * Unit Tests for dlp commands.
 */
class dlpLongRunningTest extends TestCase
{
    use TestTrait;
    use ProphecyTrait;

    private static $dataset = 'integration_tests_dlp';
    private static $table = 'harmful';
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

    public function testInspectDatastore()
    {
        $kind = 'Person';
        $namespace = 'DLP';

        $output = $this->runFunctionSnippet('inspect_datastore', [
            self::$projectId,
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            $kind,
            $namespace
        ]);
        $this->assertStringContainsString('PERSON_NAME', $output);
    }

    public function testInspectBigquery()
    {
        $output = $this->runFunctionSnippet('inspect_bigquery', [
            self::$projectId,
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
        ]);
        $this->assertStringContainsString('PERSON_NAME', $output);
    }

    public function testInspectGCS()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $objectName = 'dlp/harmful.csv';
        $topicId = self::$topic->name();
        $subscriptionId = self::$subscription->name();

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $dlpJobResponse = $this->dlpJobResponse();
        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['createDlpJob']);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($dlpJobResponse['getDlpJob']);

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
            "dlp_inspect_gcs('%s','%s','%s','%s','%s');",
            self::$projectId,
            $topicId,
            $subscriptionId,
            $bucketName,
            $objectName,
        );

        $tmpFile = $this->writeTempSample('inspect_gcs', [
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

    public function testNumericalStats()
    {
        $columnName = 'Age';

        $output = $this->runFunctionSnippet('numerical_stats', [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $columnName,
        ]);

        $this->assertMatchesRegularExpression('/Value range: \[\d+, \d+\]/', $output);
        $this->assertMatchesRegularExpression('/Value at \d+ quantile: \d+/', $output);
    }

    public function testCategoricalStats()
    {
        $columnName = 'Gender';

        $output = $this->runFunctionSnippet('categorical_stats', [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $columnName,
        ]);

        $this->assertMatchesRegularExpression('/Most common value occurs \d+ time\(s\)/', $output);
        $this->assertMatchesRegularExpression('/Least common value occurs \d+ time\(s\)/', $output);
        $this->assertMatchesRegularExpression('/\d+ unique value\(s\) total/', $output);
    }

    public function testKAnonymity()
    {
        $quasiIds = 'Age,Gender';

        $output = $this->runFunctionSnippet('k_anonymity', [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $quasiIds,
        ]);
        $this->assertStringContainsString('{"stringValue":"Female"}', $output);
        $this->assertMatchesRegularExpression('/Class size: \d/', $output);
    }

    public function testLDiversity()
    {
        $sensitiveAttribute = 'Name';
        $quasiIds = 'Age,Gender';

        $output = $this->runFunctionSnippet('l_diversity', [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $sensitiveAttribute,
            $quasiIds,
        ]);
        $this->assertStringContainsString('{"stringValue":"Female"}', $output);
        $this->assertMatchesRegularExpression('/Class size: \d/', $output);
        $this->assertStringContainsString('{"stringValue":"James"}', $output);
    }

    public function testKMap()
    {
        $regionCode = 'US';
        $quasiIds = 'Age,Gender';
        $infoTypes = 'AGE,GENDER';

        $output = $this->runFunctionSnippet('k_map', [
            self::$projectId,
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $regionCode,
            $quasiIds,
            $infoTypes,
        ]);
        $this->assertMatchesRegularExpression('/Anonymity range: \[\d, \d\]/', $output);
        $this->assertMatchesRegularExpression('/Size: \d/', $output);
        $this->assertStringContainsString('{"stringValue":"Female"}', $output);
    }
}
