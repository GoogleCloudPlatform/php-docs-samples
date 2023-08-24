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

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setInspectDetails((new InspectDataSourceDetails())
                ->setResult((new Result())
                    ->setInfoTypeStats([
                        (new InfoTypeStats())
                            ->setInfoType((new InfoType())->setName('PERSON_NAME'))
                            ->setCount(3),
                        (new InfoTypeStats())
                            ->setInfoType((new InfoType())->setName('CREDIT_CARD_NUMBER'))
                            ->setCount(3)
                    ])));

        $dlpServiceClientMock->createDlpJob(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($createDlpJobResponse);

        $dlpServiceClientMock->getDlpJob(Argument::any())
            ->shouldBeCalled()
            ->willReturn($getDlpJobResponse);

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
            ->willReturn(['DlpJobName' => 'projects/' . self::$projectId . '/dlpJobs/job-name-123']);

        $subscriptionMock->acknowledge(Argument::any())
            ->shouldBeCalled()
            ->willReturn($messageMock->reveal());

        // Creating a temp file for testing.
        $sampleFile = __DIR__ . '/../src/inspect_gcs.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'inspect_gcs' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
                    self::$projectId,
                    $topicId,
                    $subscriptionId,
                    $bucketName,
                    $objectName,
                ]);

        // delete topic , subscription , and temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('infoType PERSON_NAME', $output);
        $this->assertStringContainsString('infoType CREDIT_CARD_NUMBER', $output);
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
