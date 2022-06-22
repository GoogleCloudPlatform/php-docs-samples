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
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
 */
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

        self::$instanceId = self::requireEnv('GOOGLE_SPANNER_INSTANCE_ID');
        self::$databaseId = 'php-test-' . time() . rand();
        self::$instance = $spanner->instance(self::$instanceId);
    }

    public function testCreateDatabase()
    {
        $output = $this->runFunctionSnippet('pg_create_database');
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
        $output = $this->runFunctionSnippet('pg_cast_data_type');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('String: 1', $output);
        $this->assertStringContainsString('Int: 2', $output);
        $this->assertStringContainsString('Decimal: 3', $output);
        $this->assertStringContainsString('Bytes: NA==', $output);
        $this->assertStringContainsString(sprintf('Float: %d', 5), $output);
        $this->assertStringContainsString('Bool: 1', $output);
        $this->assertStringContainsString('Timestamp: 2021-11-03T09:35:01.000000Z', $output);
    }

    /*
     * @depends testCreateDatabase
     */
    public function testFunctions()
    {
        $output = $this->runFunctionSnippet('pg_functions');
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString('1284352323 seconds after epoch is 2010-09-13T04:32:03.000000Z', $output);
    }

    /*
     * @depends testCreateDatabase
     */
    public function testCreateTableCaseSensitivity()
    {
        $tableName = 'Singers' . time() . rand();
        $output = $this->runFunctionSnippet('pg_case_sensitivity', [
            self::$instanceId, self::$databaseId, $tableName
        ]);
        self::$lastUpdateDataTimestamp = time();
        $expected = sprintf('Created %s table in database %s on instance %s',
            $tableName, self::$databaseId, self::$instanceId);

        $this->assertStringContainsString($expected, $output);
    }

    /*
     * @depends testCreateTableCaseSensitivity
     */
    public function testInformationSchema()
    {
        $output = $this->runFunctionSnippet('pg_information_schema');
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString(sprintf('table_catalog: %s', self::$databaseId), $output);
        $this->assertStringContainsString('table_schema: public', $output);
        $this->assertStringContainsString('table_name: venues', $output);
    }

    /**
     * @depends testCreateTableCaseSensitivity
     */
    public function testDmlWithParams()
    {
        $output = $this->runFunctionSnippet('pg_dml_with_params');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Inserted 2 singer(s).', $output);
    }

    /**
     * @depends testCreateTableCaseSensitivity
     */
    public function testBatchDml()
    {
        // delete anything in singers table before running the sample
        // to avoid collision of IDs
        $database = self::$instance->database(self::$databaseId);
        $database->executePartitionedUpdate('DELETE FROM Singers WHERE singerid IS NOT NULL');

        $output = $this->runFunctionSnippet('pg_batch_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Inserted 2 singers using Batch DML.', $output);
    }

    /**
     * @depends testBatchDml
     */
    public function testQueryParameter()
    {
        $output = $this->runFunctionSnippet('pg_query_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('SingerId: 2, Firstname: Bruce, LastName: Allison', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testPartitionedDml()
    {
        // setup some data
        $db = self::$instance->database(self::$databaseId);
        $op = $db->updateDdl('
        CREATE TABLE users (
            id  bigint NOT NULL PRIMARY KEY,
            name     varchar(1024) NOT NULL,
            active boolean
        )');
        $op->pollUntilComplete();

        $db->runTransaction(function (Transaction $t) {
            $t->executeUpdate('INSERT INTO users (id, name, active)'
                . ' VALUES ($1, $2, $3), ($4, $5, $6)',
                [
                    'parameters' => [
                        'p1' => 1,
                        'p2' => 'Alice',
                        'p3' => true,
                        'p4' => 2,
                        'p5' => 'Bruce',
                        'p6' => false,
                    ]
                ]);
            $t->commit();
        });

        $output = $this->runFunctionSnippet('pg_partitioned_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Deleted 1 inactive user(s).', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testAddColumn()
    {
        $output = $this->runFunctionSnippet('pg_add_column');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Added column MarketingBudget on table Albums', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInterleavedTable()
    {
        $parentTable = 'Singers' . time() . rand();
        $childTable = 'Albumbs' . time() . rand();

        $output = $this->runFunctionSnippet('pg_interleaved_table', [
            self::$instanceId, self::$databaseId, $parentTable, $childTable
        ]);
        self::$lastUpdateDataTimestamp = time();

        $this->assertStringContainsString('Created interleaved table hierarchy using PostgreSQL dialect', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testNumericDataType()
    {
        $tableName = 'Venues' . time() . rand();
        $output = $this->runFunctionSnippet('pg_numeric_data_type', [
            self::$instanceId, self::$databaseId, $tableName
        ]);
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

        $output = $this->runFunctionSnippet('pg_order_nulls', [
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

    public function testIndexCreateSorting()
    {
        $output = $this->runFunctionSnippet('pg_create_storing_index');
        $this->assertStringContainsString('Added the AlbumsByAlbumTitle index.', $output);
    }

    public function testDmlGettingStartedUpdate()
    {
        // setup with some data
        $db = self::$instance->database(self::$databaseId);
        $db->runTransaction(function (Transaction $t) {
            $t->executeUpdateBatch([
                [
                    'sql' => 'INSERT INTO Albums (SingerId, AlbumId, MarketingBudget) VALUES($1, $2, $3)',
                    'parameters' => [
                        'p1' => 1,
                        'p2' => 1,
                        'p3' => 0
                    ]
                ],
                [
                    'sql' => 'INSERT INTO Albums (SingerId, AlbumId, MarketingBudget) VALUES($1, $2, $3)',
                    'parameters' => [
                        'p1' => 2,
                        'p2' => 2,
                        'p3' => 200001
                    ]
                ]
            ]);

            $t->commit();
        });

        $output = $this->runFunctionSnippet('pg_dml_getting_started_update');
        $this->assertStringContainsString('Marketing budget updated.', $output);
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up
        if (self::$instance->exists()) {
            $database = self::$instance->database(self::$databaseId);
            $database->drop();
        }
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_values($params) ?: [self::$instanceId, self::$databaseId]
        );
    }
}
