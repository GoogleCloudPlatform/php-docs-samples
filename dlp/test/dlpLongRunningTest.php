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
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\CategoricalStatsResult;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\CategoricalStatsResult\CategoricalStatsHistogramBucket;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult\KAnonymityEquivalenceClass;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KAnonymityResult\KAnonymityHistogramBucket;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KMapEstimationResult;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KMapEstimationResult\KMapEstimationHistogramBucket;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\KMapEstimationResult\KMapEstimationQuasiIdValues;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult\LDiversityEquivalenceClass;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult\LDiversityHistogramBucket;
use Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\NumericalStatsResult;
use Google\Cloud\Dlp\V2\InspectDataSourceDetails\Result;
use Google\Cloud\Dlp\V2\Value;
use Google\Cloud\Dlp\V2\ValueFrequency;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Message;

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
                            ->setInfoType((new InfoType())->setName('PHONE_NUMBER'))
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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/inspect_datastore.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'inspect_datastore' => $tmpFileName
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
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            $kind,
            $namespace
        ]);

        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('PERSON_NAME', $output);
        $this->assertStringContainsString('PHONE_NUMBER', $output);
    }

    public function testInspectBigquery()
    {
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
                            ->setCount(2)
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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/inspect_bigquery.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'inspect_bigquery' => $tmpFileName
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
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
        ]);
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('PERSON_NAME', $output);
    }

    public function testInspectGCS()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $objectName = 'dlp/harmful.csv';

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
            self::$topic->name(),
            self::$subscription->name(),
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

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setRiskDetails((new AnalyzeDataSourceRiskDetails())
                    ->setNumericalStatsResult((new NumericalStatsResult())
                            ->setMinValue((new Value())->setIntegerValue(1231))
                            ->setMaxValue((new Value())->setIntegerValue(9999))
                            ->setQuantileValues([
                                (new Value())->setIntegerValue(1231),
                                (new Value())->setIntegerValue(1231),
                                (new Value())->setIntegerValue(1231),
                                (new Value())->setIntegerValue(1234),
                                (new Value())->setIntegerValue(1234),
                                (new Value())->setIntegerValue(3412),
                                (new Value())->setIntegerValue(3412),
                                (new Value())->setIntegerValue(4444),
                                (new Value())->setIntegerValue(9999),
                            ])
                    )
            );

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/numerical_stats.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'numerical_stats' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent,
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $columnName,
        ]);
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertMatchesRegularExpression('/Value range: \[\d+, \d+\]/', $output);
        $this->assertMatchesRegularExpression('/Value at \d+ quantile: \d+/', $output);
    }

    public function testCategoricalStats()
    {
        $columnName = 'Gender';

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setRiskDetails((new AnalyzeDataSourceRiskDetails())
                    ->setCategoricalStatsResult((new CategoricalStatsResult())
                            ->setValueFrequencyHistogramBuckets([
                                (new CategoricalStatsHistogramBucket())
                                    ->setValueFrequencyUpperBound(1)
                                    ->setValueFrequencyLowerBound(1)
                                    ->setBucketSize(1)
                                    ->setBucketValues([
                                        (new ValueFrequency())
                                            ->setValue((new Value())->setStringValue('{"stringValue":"19"}'))
                                            ->setCount(1),
                                    ]),
                            ])
                    )
            );

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/categorical_stats.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'categorical_stats' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent,
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $columnName,
        ]);
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertMatchesRegularExpression('/Most common value occurs \d+ time\(s\)/', $output);
        $this->assertMatchesRegularExpression('/Least common value occurs \d+ time\(s\)/', $output);
        $this->assertMatchesRegularExpression('/\d+ unique value\(s\) total/', $output);
    }

    public function testKAnonymity()
    {
        $quasiIdNames = ['Age', 'Gender'];

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
                            ->setEquivalenceClassHistogramBuckets([
                                (new KAnonymityHistogramBucket())
                                    ->setEquivalenceClassSizeLowerBound(1)
                                    ->setEquivalenceClassSizeUpperBound(1)
                                    ->setBucketValues([
                                        (new KAnonymityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"19"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEquivalenceClassSize(1),
                                        (new KAnonymityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEquivalenceClassSize(1)

                                    ]),
                                (new KAnonymityHistogramBucket())
                                    ->setEquivalenceClassSizeLowerBound(2)
                                    ->setEquivalenceClassSizeUpperBound(2)
                                    ->setBucketValues([
                                        (new KAnonymityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Female"}')
                                            ])
                                            ->setEquivalenceClassSize(2)
                                    ])
                            ])
                    )
            );

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/k_anonymity.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'k_anonymity' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent,
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $quasiIdNames
        ]);
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('Job projects/' . self::$projectId . '/dlpJobs/', $output);
        $this->assertStringContainsString('{\"stringValue\":\"Female\"}', $output);
        $this->assertMatchesRegularExpression('/Class size: \d/', $output);
    }

    public function testLDiversity()
    {
        $sensitiveAttribute = 'Name';
        $quasiIds = 'Age,Gender';

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setRiskDetails((new AnalyzeDataSourceRiskDetails())
                    ->setLDiversityResult((new LDiversityResult())
                            ->setSensitiveValueFrequencyHistogramBuckets([
                                (new LDiversityHistogramBucket())
                                    ->setSensitiveValueFrequencyLowerBound(1)
                                    ->setSensitiveValueFrequencyUpperBound(1)
                                    ->setBucketValues([
                                        (new LDiversityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"19"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEquivalenceClassSize(1)
                                            ->setTopSensitiveValues([
                                                (new ValueFrequency())
                                                    ->setValue((new Value())->setStringValue('{"stringValue":"James"}'))
                                                    ->setCount(1)
                                            ]),
                                        (new LDiversityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEquivalenceClassSize(1)
                                            ->setTopSensitiveValues([
                                                (new ValueFrequency())
                                                    ->setValue((new Value())->setStringValue('{"stringValue":"Joe"}'))
                                                    ->setCount(1)
                                            ]),
                                    ]),
                                (new LDiversityHistogramBucket())
                                    ->setSensitiveValueFrequencyLowerBound(2)
                                    ->setSensitiveValueFrequencyUpperBound(2)
                                    ->setBucketValues([
                                        (new LDiversityEquivalenceClass())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Female"}')
                                            ])
                                            ->setEquivalenceClassSize(1)
                                            ->setTopSensitiveValues([
                                                (new ValueFrequency())
                                                    ->setValue((new Value())->setStringValue('{"stringValue":"Carrie"}'))
                                                    ->setCount(2),
                                                (new ValueFrequency())
                                                    ->setValue((new Value())->setStringValue('{"stringValue":"Marie"}'))
                                                    ->setCount(1)
                                            ]),
                                    ]),
                            ])
                    )
            );

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/l_diversity.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'l_diversity' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent,
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
            self::$projectId, // calling project
            self::$projectId, // data project
            self::$topic->name(),
            self::$subscription->name(),
            self::$dataset,
            self::$table,
            $sensitiveAttribute,
            $quasiIds,
        ]);
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertStringContainsString('{\"stringValue\":\"Female\"}', $output);
        $this->assertMatchesRegularExpression('/Class size: \d/', $output);
        $this->assertStringContainsString('{\"stringValue\":\"James\"}', $output);
    }

    public function testKMap()
    {
        $regionCode = 'US';
        $quasiIds = 'Age,Gender';
        $infoTypes = 'AGE,GENDER';

        // Mock the necessary objects and methods
        $dlpServiceClientMock = $this->prophesize(DlpServiceClient::class);

        $createDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::PENDING);

        $getDlpJobResponse = (new DlpJob())
            ->setName('projects/' . self::$projectId . '/dlpJobs/job-name-123')
            ->setState(JobState::DONE)
            ->setRiskDetails((new AnalyzeDataSourceRiskDetails())
                    ->setKMapEstimationResult((new KMapEstimationResult())
                            ->setKMapEstimationHistogram([
                                (new KMapEstimationHistogramBucket())
                                    ->setMinAnonymity(3)
                                    ->setMaxAnonymity(3)
                                    ->setBucketSize(3)
                                    ->setBucketValues([
                                        (new KMapEstimationQuasiIdValues())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"integerValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Female"}')
                                            ])
                                            ->setEstimatedAnonymity(3),
                                    ]),
                                (new KMapEstimationHistogramBucket())
                                    ->setMinAnonymity(1)
                                    ->setMaxAnonymity(1)
                                    ->setBucketSize(2)
                                    ->setBucketValues([
                                        (new KMapEstimationQuasiIdValues())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"integerValue":"19"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEstimatedAnonymity(1),
                                        (new KMapEstimationQuasiIdValues())
                                            ->setQuasiIdsValues([
                                                (new Value())
                                                    ->setStringValue('{"integerValue":"35"}'),
                                                (new Value())
                                                    ->setStringValue('{"stringValue":"Male"}')
                                            ])
                                            ->setEstimatedAnonymity(1),
                                    ]),
                            ])
                    )
            );

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
        $pubSubClientMock->topic(self::$topic->name())
            ->shouldBeCalled()
            ->willReturn($topicMock->reveal());

        $topicMock->name()
            ->shouldBeCalled()
            ->willReturn('projects/' . self::$projectId . '/topics/' . self::$topic->name());

        $topicMock->subscription(self::$subscription->name())
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
        $sampleFile = __DIR__ . '/../src/k_map.php';
        $tmpFileName = basename($sampleFile, '.php') . '_temp';
        $tmpFilePath = __DIR__ . '/../src/' . $tmpFileName . '.php';

        $fileContent = file_get_contents($sampleFile);
        $replacements = [
            '$dlp = new DlpServiceClient();' => 'global $dlp;',
            '$pubsub = new PubSubClient();' => 'global $pubsub;',
            'k_map' => $tmpFileName
        ];
        $fileContent = strtr($fileContent, $replacements);
        $tmpFile = file_put_contents(
            $tmpFilePath,
            $fileContent,
        );
        global $dlp;
        global $pubsub;

        $dlp = $dlpServiceClientMock->reveal();
        $pubsub = $pubSubClientMock->reveal();

        // Call the method under test
        $output = $this->runFunctionSnippet($tmpFileName, [
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
        // delete temp file
        unlink($tmpFilePath);

        // Assert the expected behavior or outcome
        $this->assertMatchesRegularExpression('/Anonymity range: \[\d, \d\]/', $output);
        $this->assertMatchesRegularExpression('/Size: \d/', $output);
        $this->assertStringContainsString('{\"stringValue\":\"Female\"}', $output);
    }
}
