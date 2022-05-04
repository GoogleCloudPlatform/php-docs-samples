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
use Google\Cloud\PubSub\PubSubClient;

/**
 * Unit Tests for dlp commands.
 */
class dlpLongRunningTest extends TestCase
{
    use TestTrait;

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

        $output = $this->runFunctionSnippet('inspect_gcs', [
            self::$projectId,
            self::$topic->name(),
            self::$subscription->name(),
            $bucketName,
            $objectName,
        ]);
        $this->assertStringContainsString('PERSON_NAME', $output);
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

        $this->assertRegExp('/Value range: \[\d+, \d+\]/', $output);
        $this->assertRegExp('/Value at \d+ quantile: \d+/', $output);
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

        $this->assertRegExp('/Most common value occurs \d+ time\(s\)/', $output);
        $this->assertRegExp('/Least common value occurs \d+ time\(s\)/', $output);
        $this->assertRegExp('/\d+ unique value\(s\) total/', $output);
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
        $this->assertRegExp('/Class size: \d/', $output);
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
        $this->assertRegExp('/Class size: \d/', $output);
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
        $this->assertRegExp('/Anonymity range: \[\d, \d\]/', $output);
        $this->assertRegExp('/Size: \d/', $output);
        $this->assertStringContainsString('{"stringValue":"Female"}', $output);
    }
}
