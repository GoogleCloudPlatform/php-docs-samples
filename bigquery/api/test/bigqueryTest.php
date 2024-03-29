<?php
/**
 * Copyright 2018 Google LLC.
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

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for BigQuery snippets.
 */
class FunctionsTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }
    use EventuallyConsistentTestTrait;

    private static $datasetId;
    private static $dataset;

    public static function setUpBeforeClass(): void
    {
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');
        $client = new BigQueryClient([
            'projectId' => self::$projectId,
        ]);
        self::$datasetId = sprintf('temp_dataset_%s', time());
        self::$dataset = $client->createDataset(self::$datasetId);
    }

    public function testBigQueryClient()
    {
        $projectId = self::$projectId;
        $bigQuery = require_once __DIR__ . '/../src/bigquery_client.php';

        $this->assertInstanceOf(
            \Google\Cloud\BigQuery\BigQueryClient::class,
            $bigQuery
        );
    }

    public function testBrowseTable()
    {
        $tableId = $this->createTempTable();
        $output = $this->runFunctionSnippet('browse_table', [
            self::$datasetId,
            $tableId,
        ]);
        $this->assertStringContainsString('Brent Shaffer', $output);
    }

    public function testCopyTable()
    {
        $sourceTableId = $this->createTempTable();
        $destinationTableId = sprintf('test_copy_table_%s', time());

        // run the import
        $output = $this->runFunctionSnippet('copy_table', [
            self::$datasetId,
            $sourceTableId,
            $destinationTableId,
        ]);

        $destinationTable = self::$dataset->table($destinationTableId);
        $this->assertStringContainsString('Table copied successfully', $output);
        $this->verifyTable($destinationTable, 'Brent Shaffer', 3);
    }

    public function testCreateAndDeleteDataset()
    {
        $tempDatasetId = sprintf('test_dataset_%s', time());
        $output = $this->runFunctionSnippet('create_dataset', [$tempDatasetId]);
        $this->assertStringContainsString('Created dataset', $output);

        // delete the dataset
        $output = $this->runFunctionSnippet('delete_dataset', [$tempDatasetId]);
        $this->assertStringContainsString('Deleted dataset', $output);
    }

    public function testCreateAndDeleteTable()
    {
        $tempTableId = sprintf('test_table_%s', time());
        $fields = json_encode([
            ['name' => 'name', 'type' => 'string', 'mode' => 'nullable'],
            ['name' => 'title', 'type' => 'string', 'mode' => 'nullable']
        ]);
        $output = $this->runFunctionSnippet('create_table', [
            self::$datasetId,
            $tempTableId,
            $fields
        ]);
        $this->assertStringContainsString('Created table', $output);

        // delete the table
        $output = $this->runFunctionSnippet('delete_table', [
            self::$datasetId,
            $tempTableId
        ]);
        $this->assertStringContainsString('Deleted table', $output);
    }

    public function testExtractTable()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $tableId = $this->createTempTable();

        // run the import
        $output = $this->runFunctionSnippet('extract_table', [
            self::$datasetId,
            $tableId,
            $bucketName
        ]);

        // verify the contents of the bucket
        $storage = new StorageClient([
            'projectId' => self::$projectId,
        ]);
        $object = $storage->bucket($bucketName)->objects(['prefix' => $tableId])->current();
        $contents = $object->downloadAsString();
        $this->assertStringContainsString('Brent Shaffer', $contents);
        $this->assertStringContainsString('Takashi Matsuo', $contents);
        $this->assertStringContainsString('Jeffrey Rennie', $contents);
        $object->delete();
        $this->assertFalse($object->exists());
    }

    public function testGetTable()
    {
        $projectId = self::$projectId;
        $datasetId = self::$datasetId;
        $tableId = $this->createTempEmptyTable();
        $table = require_once __DIR__ . '/../src/get_table.php';

        $this->assertInstanceOf(
            \Google\Cloud\BigQuery\Table::class,
            $table
        );
    }

    public function testImportFromFile()
    {
        $source = __DIR__ . '/data/test_data.csv';

        // create the temp table to import
        $tempTableId = $this->createTempEmptyTable();

        // run the import
        $output = $this->runFunctionSnippet('import_from_local_csv', [
            self::$datasetId,
            $tempTableId,
            $source,
        ]);

        $tempTable = self::$dataset->table($tempTableId);
        $this->assertStringContainsString('Data imported successfully', $output);
        $this->verifyTable($tempTable, 'Brent Shaffer', 3);
    }

    /**
     * @dataProvider provideImportFromStorage
     */
    public function testImportFromStorage($snippet, $runTruncateSnippet = false)
    {
        $tableId = sprintf('%s_%s', $snippet, rand());

        // run the import
        $output = $this->runFunctionSnippet($snippet, [
            self::$datasetId,
            $tableId,
        ]);

        $this->assertStringContainsString('Data imported successfully', $output);

        // verify table contents
        $table = self::$dataset->table($tableId);
        $this->verifyTable($table, 'Washington', 50);

        if ($runTruncateSnippet) {
            $truncateSnippet = sprintf('%s_truncate', $snippet);
            $output = $this->runFunctionSnippet($truncateSnippet, [
                self::$datasetId,
                $tableId,
            ]);
            $this->assertStringContainsString('Data imported successfully', $output);
            $this->verifyTable($table, 'Washington', 50);
        }
    }

    public function provideImportFromStorage()
    {
        return [
            ['import_from_storage_csv', true],
            ['import_from_storage_json', true],
            ['import_from_storage_orc', true],
            ['import_from_storage_parquet', true],
            ['import_from_storage_csv_autodetect'],
            ['import_from_storage_json_autodetect'],
        ];
    }

    public function testInsertSql()
    {
        // create the temp table to import
        $tempTableId = $this->createTempEmptyTable();

        // Write a temp file so we use the temp table in the sql source
        file_put_contents(
            $tmpFile = sprintf('%s/%s.sql', sys_get_temp_dir(), $tempTableId),
            strtr(
                file_get_contents(__DIR__ . '/data/test_data.sql'),
                ['test_table' => $tempTableId]
            )
        );

        // run the import
        $output = $this->runFunctionSnippet('insert_sql', [
            self::$datasetId,
            $tmpFile,
        ]);

        $tempTable = self::$dataset->table($tempTableId);
        $this->assertStringContainsString('Data imported successfully', $output);
        $this->verifyTable($tempTable, 'Brent Shaffer', 3);
    }

    public function testListDatasets()
    {
        $output = $this->runFunctionSnippet('list_datasets');
        $this->assertStringContainsString(self::$datasetId, $output);
    }

    public function testListTables()
    {
        $tempTableId = $this->createTempEmptyTable();
        $output = $this->runFunctionSnippet('list_tables', [self::$datasetId]);
        $this->assertStringContainsString($tempTableId, $output);
    }

    public function testStreamRow()
    {
        $tempTableId = $this->createTempEmptyTable();
        $data = json_encode(['name' => 'Brent Shaffer', 'title' => 'Developer']);
        // run the import
        $output = $this->runFunctionSnippet('stream_row', [
            self::$datasetId,
            $tempTableId,
            $data
        ]);

        $tempTable = self::$dataset->table($tempTableId);
        $this->assertStringContainsString('Data streamed into BigQuery successfully', $output);
        $this->verifyTable($tempTable, 'Brent Shaffer', 1);
    }

    public function testRunQuery()
    {
        $query = 'SELECT corpus, COUNT(*) as unique_words
            FROM `publicdata.samples.shakespeare`
            GROUP BY corpus
            ORDER BY unique_words DESC
            LIMIT 10';

        $output = $this->runFunctionSnippet('run_query', [$query]);
        $this->assertStringContainsString('hamlet', $output);
        $this->assertStringContainsString('kinglear', $output);
        $this->assertStringContainsString('Found 10 row(s)', $output);
    }

    public function testTableInsertRowsExplicitNoneInsertIds()
    {
        $tempTableId = $this->createTempEmptyTable();

        $output = $this->runFunctionSnippet('table_insert_rows_explicit_none_insert_ids', [
            self::$datasetId,
            $tempTableId,
            json_encode(['name' => 'Yash Sahu', 'title' => 'Noogler dev']),
            json_encode(['name' => 'Friday', 'title' => 'Are the best'])
        ]);

        $tempTable = self::$dataset->table($tempTableId);
        $expectedOutput = sprintf('Rows successfully inserted into table without insert ids');
        $this->assertStringContainsString($expectedOutput, $output);
        $this->verifyTable($tempTable, 'Yash Sahu', 2);
    }

    public function testRunQueryAsJob()
    {
        $tableId = $this->createTempTable();
        $query = sprintf(
            'SELECT * FROM `%s.%s` LIMIT 1',
            self::$datasetId,
            $tableId
        );

        $output = $this->runFunctionSnippet('run_query_as_job', [$query]);
        $this->assertStringContainsString('Found 1 row(s)', $output);
    }

    public function testDryRunQuery()
    {
        $tableId = $this->createTempTable();
        $query = sprintf(
            'SELECT * FROM `%s.%s` LIMIT 1',
            self::$datasetId,
            $tableId
        );

        $output = $this->runFunctionSnippet('dry_run_query', [$query]);
        $this->assertStringContainsString('This query will process 126 bytes', $output);
    }

    public function testQueryNoCache()
    {
        $tableId = $this->createTempTable();
        $query = sprintf(
            'SELECT * FROM `%s.%s` LIMIT 1',
            self::$datasetId,
            $tableId
        );

        $output = $this->runFunctionSnippet('query_no_cache', [$query]);
        $this->assertStringContainsString('Found 1 row(s)', $output);
    }

    public function testQueryLegacy()
    {
        $output = $this->runFunctionSnippet('query_legacy');
        $this->assertStringContainsString('tempest', $output);
        $this->assertStringContainsString('kinghenryviii', $output);
        $this->assertStringContainsString('Found 42 row(s)', $output);
    }

    public function testAddColumnLoadAppend()
    {
        $tableId = $this->createTempTable();
        $output = $this->runFunctionSnippet('add_column_load_append', [
            self::$datasetId,
            $tableId
        ]);

        $this->assertStringContainsString('name', $output);
        $this->assertStringContainsString('title', $output);
        $this->assertStringContainsString('description', $output);
    }

    public function testAddColumnQueryAppend()
    {
        $tableId = $this->createTempTable();
        $output = $this->runFunctionSnippet('add_column_query_append', [
            self::$datasetId,
            $tableId
        ]);
        $this->assertStringContainsString('name', $output);
        $this->assertStringContainsString('title', $output);
        $this->assertStringContainsString('description', $output);
    }

    public function testUndeleteTable()
    {
        // Create a base table
        $sourceTableId = $this->createTempTable();

        // run the sample
        $restoredTableId = uniqid('restored_');
        $output = $this->runFunctionSnippet('undelete_table', [
            self::$datasetId,
            $sourceTableId,
            $restoredTableId,
        ]);

        $restoredTable = self::$dataset->table($restoredTableId);
        $this->assertStringContainsString('Snapshot restored successfully', $output);
        $this->verifyTable($restoredTable, 'Brent Shaffer', 3);
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        array_unshift($params, self::$projectId);
        return $this->traitRunFunctionSnippet(
            $sampleName,
            $params
        );
    }

    private function createTempEmptyTable()
    {
        $tempTableId = sprintf('test_table_%s_%s', time(), rand());
        $fields = json_encode([
            ['name' => 'name', 'type' => 'string', 'mode' => 'nullable'],
            ['name' => 'title', 'type' => 'string', 'mode' => 'nullable']
        ]);
        $this->runFunctionSnippet('create_table', [
            self::$datasetId,
            $tempTableId,
            $fields
        ]);
        return $tempTableId;
    }

    private function createTempTable()
    {
        $tempTableId = $this->createTempEmptyTable();
        $source = __DIR__ . '/data/test_data.csv';
        $output = $this->runFunctionSnippet('import_from_local_csv', [
            self::$datasetId,
            $tempTableId,
            $source,
        ]);
        return $tempTableId;
    }

    private function verifyTable($table, $expectedValue, $expectedRowCount)
    {
        $testFunction = function () use ($table, $expectedValue, $expectedRowCount) {
            $numRows = 0;
            $foundValue = false;
            foreach ($table->rows([]) as $row) {
                foreach ($row as $column => $value) {
                    if ($value == $expectedValue) {
                        $foundValue = true;
                    }
                }
                $numRows++;
            }
            $this->assertTrue($foundValue);
            $this->assertEquals($numRows, $expectedRowCount);
        };
        $this->runEventuallyConsistentTest($testFunction);
    }

    public static function tearDownAfterClass(): void
    {
        self::$dataset->delete(['deleteContents' => true]);
    }
}
