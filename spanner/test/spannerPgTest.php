<?php
/**
 * Copyright 2022 Google LLC
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

namespace Google\Cloud\Samples\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

class spannerPgTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    use RetryTrait, EventuallyConsistentTestTrait;

    /** @var string instanceId */
    protected static $instanceId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var Instance $instance */
    protected static $instance;

    /** @var $lastUpdateData int */
    protected static $lastUpdateDataTimestamp;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }

        $spanner = new SpannerClient([
            'projectId' => self::$projectId
        ]);

        self::$instanceId = 'php-test-' . time() . rand();
        self::$databaseId = 'php-test-' . time() . rand();
        $instanceRegion = 'regional-us-west2';
        self::createInstance($spanner, self::$instanceId, $instanceRegion);
        self::$instance = $spanner->instance(self::$instanceId);
    }

    public function testCreateDatabase()
    {
        $output = $this->runFunctionSnippet('pg_spanner_create_database');
        self::$lastUpdateDataTimestamp = time();
        $expected = sprintf('Created database %s with dialect POSTGRESQL on instance %s',
            self::$databaseId, self::$instanceId);

        $this->assertStringContainsString($expected, $output);
    }

    /*
     * @depends testCreateDatabase
     */
    public function testCastDataType()
    {
        $output = $this->runFunctionSnippet('pg_spanner_cast_data_type');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('String: 1', $output);
        $this->assertStringContainsString('Int: 2', $output);
        $this->assertStringContainsString('Decimal: 3', $output);
        $this->assertStringContainsString('Bytes: NA==', $output);
        $this->assertStringContainsString(sprintf('Float: %d', 5), $output);
        $this->assertStringContainsString('Bool: 1', $output);
        $this->assertStringContainsString('Timestamp: 2022-03-03T00:00:00.000000Z', $output);
    }

    /*
     * @depends testCreateDatabase
     */
    public function testFunctions()
    {
        $output = $this->runFunctionSnippet('pg_spanner_functions');
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString('1284352323 seconds after epoch is 2010-09-13T04:32:03.000000Z', $output);
    }

    /*
     * @depends testCreateDatabase
     */
    public function testCreateTableCaseSensitivity()
    {
        $output = $this->runFunctionSnippet('pg_spanner_case_sensitivity');
        self::$lastUpdateDataTimestamp = time();
        $expected = sprintf('Created Singers table in database %s on instance %s',
            self::$databaseId, self::$instanceId);

        $this->assertStringContainsString($expected, $output);
    }

    /*
     * @depends testCreateTableCaseSensitivity
     */
    public function testInformationSchema()
    {
        $output = $this->runFunctionSnippet('pg_spanner_information_schema');
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString(sprintf('table_catalog: %s', self::$databaseId), $output);
        $this->assertStringContainsString('table_schema: public', $output);
        $this->assertStringContainsString('table_name: singers', $output);
    }

    /**
     * @depends testCreateTableCaseSensitivity
     */
    public function testDmlWithParams()
    {
        $output = $this->runFunctionSnippet('pg_spanner_dml_with_params');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Inserted 1 singer(s).', $output);
    }

    /**
     * @depends testCreateTableCaseSensitivity
     */
    public function testBatchDml()
    {
        $output = $this->runFunctionSnippet('pg_spanner_batch_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Inserted 2 singers using Batch DML.', $output);
    }

    /**
     * @depends testBatchDml
     */
    public function testQueryParameter()
    {
        $output = $this->runFunctionSnippet('pg_spanner_query_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('SingerId: 2, Firstname: Bruce, LastName: Allison', $output);
    }

    /**
     * @depends testBatchDml
     */
    public function testPartitionedDml()
    {
        $db = self::$instance->database(self::$databaseId);
        $results = $db->execute('SELECT count(*) FROM Singers WHERE "FirstName" IS NOT NULL');
        $count = $results->rows()->current()['count'];

        $output = $this->runFunctionSnippet('pg_spanner_partitioned_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString(sprintf('Deleted %s rows.', $count), $output);
    }

    /**
     * @depends testCreateTableCaseSensitivity
     */
    public function testPgAddColumn()
    {
        $output = $this->runFunctionSnippet('pg_spanner_query_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Added column SingerAge on table Singers', $output);
    }

    /**
     * @depends testBatchDml
     */
    public function testInterleavedTable()
    {
        $tableName = 'Singers' . time() . rand();

        $output = $this->runFunctionSnippet('pg_spanner_interleaved_table', [
            self::$instanceId, self::$databaseId, $tableName
        ]);
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString('Created interleaved table hierarchy using PostgreSQL dialect', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testNumericDataType()
    {
        $output = $this->runFunctionSnippet('pg_spanner_numeric_data_type');
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString('Inserted 1 venue(s).', $output);
        $this->assertStringContainsString('Inserted 1 venue(s) with NULL revenue.', $output);
        $this->assertStringContainsString('Inserted 1 venue(s) with NaN revenue.', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testOrderNulls()
    {
        $tableName = 'Singers' . time() . rand();

        $output = $this->runFunctionSnippet('pg_spanner_order_nulls', [
            self::$instanceId, self::$databaseId, $tableName
        ]);
        self::$lastUpdateDataTimestamp = time();

        $expected = 'Creating the table...' . PHP_EOL
            . 'Singers table created...' . PHP_EOL
            . 'Added 3 singers' . PHP_EOL
            . 'SingerId: 2, Name: Alice' . PHP_EOL
            . 'SingerId: 1, Name: Bruce' . PHP_EOL
            . 'SingerId: 3, Name: NULL' . PHP_EOL
            . 'SingerId: 3, Name: NULL' . PHP_EOL
            . 'SingerId: 1, Name: Bruce' . PHP_EOL
            . 'SingerId: 2, Name: Alice' . PHP_EOL
            . 'SingerId: 3, Name: NULL' . PHP_EOL
            . 'SingerId: 2, Name: Alice' . PHP_EOL
            . 'SingerId: 1, Name: Bruce' . PHP_EOL
            . 'SingerId: 1, Name: Bruce' . PHP_EOL
            . 'SingerId: 2, Name: Alice' . PHP_EOL
            . 'SingerId: 3, Name: NULL' . PHP_EOL;

        $this->assertEquals($expected, $output);
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up
        if (self::$instance->exists()) {
            $database = self::$instance->database(self::$databaseId);
            $database->drop();
        }
        self::$instance->delete();
    }

    private static function createInstance($spannerClient, $instanceId, $region)
    {
        $instanceConfig = $spannerClient->instanceConfiguration(
            $region
        );

        $operation = $spannerClient->createInstance(
            $instanceConfig,
            $instanceId,
            [
                'displayName' => 'This is a display name.',
                'nodeCount' => 1,
                'labels' => [
                    'cloud_spanner_samples' => true,
                ]
            ]
        );
        $operation->pollUntilComplete();
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_values($params) ?: [self::$instanceId, self::$databaseId]
        );
    }
}
