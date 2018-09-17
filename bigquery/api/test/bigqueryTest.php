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
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $datasetId;
    private static $dataset;

    public static function setUpBeforeClass()
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
        $bigQuery = require __DIR__ . '/../snippets/bigquery_client.php';

        $this->assertInstanceOf(
            \Google\Cloud\BigQuery\BigQueryClient::class,
            $bigQuery
        );
    }

    public function testBrowseTable()
    {
        $tableId = $this->createTempTable();
        $output = $this->runSnippet('browse_table', [
            self::$datasetId,
            $tableId,
        ]);
        $this->assertContains('Brent Shaffer', $output);
    }

    public function testCopyTable()
    {
        $sourceTableId = $this->createTempTable();
        $destinationTableId = sprintf('test_copy_table_%s', time());

        // run the import
        $output = $this->runSnippet('copy_table', [
            self::$datasetId,
            $sourceTableId,
            $destinationTableId,
        ]);

        $this->assertContains('Table copied successfully', $output);
        $this->verifyTempTable($destinationTableId);
    }

    public function testCreateAndDeleteDataset()
    {
        $tempDatasetId = sprintf('test_dataset_%s', time());
        $output = $this->runSnippet('create_dataset', [$tempDatasetId]);
        $this->assertContains('Created dataset', $output);

        // delete the dataset
        $output = $this->runSnippet('delete_dataset', [$tempDatasetId]);
        $this->assertContains('Deleted dataset', $output);
    }

    public function testCreateAndDeleteTable()
    {
        $tempTableId = sprintf('test_table_%s', time());
        $output = $this->runSnippet('create_table', [
            self::$datasetId,
            $tempTableId
        ]);
        $this->assertContains('Created table', $output);

        // delete the table
        $output = $this->runSnippet('delete_table', [
            self::$datasetId,
            $tempTableId
        ]);
        $this->assertContains('Deleted table', $output);
    }

    /**
     * @dataProvider provideExtractTable
     */
    public function testExtractTable($objectName, $format)
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $tableId = $this->createTempTable();

        // run the import
        $output = $this->runSnippet('extract_table', [
            self::$datasetId,
            $tableId,
            $bucketName,
            $objectName,
            $format,
        ]);

        $this->assertContains('Data extracted successfully', $output);

        // verify the contents of the bucket
        $storage = new StorageClient([
            'projectId' => self::$projectId,
        ]);
        $object = $storage->bucket($bucketName)->object($objectName);
        $contents = $object->downloadAsString();
        $this->assertContains('Brent Shaffer', $contents);
        $this->assertContains('Takashi Matsuo', $contents);
        $this->assertContains('Jeffrey Rennie', $contents);
        $object->delete();
        $this->assertFalse($object->exists());
    }

    public function provideExtractTable()
    {
        $time = time();

        return [
            [sprintf('bigquery/test_data_%s.json', $time), 'json'],
            [sprintf('bigquery/test_data_%s.csv', $time), 'csv'],
        ];
    }

    public function testGetTable()
    {
        $projectId = self::$projectId;
        $datasetId = self::$datasetId;
        $tableId = $this->createTempEmptyTable();
        $table = require __DIR__ . '/../snippets/get_table.php';

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
        $output = $this->runSnippet('import_from_local_csv', [
            self::$datasetId,
            $tempTableId,
            $source,
        ]);

        $this->assertContains('Data imported successfully', $output);
        $this->verifyTempTable($tempTableId);
    }

    /**
     * @dataProvider provideImportFromStorage
     */
    public function testImportFromStorage($snippet)
    {
        // run the import
        $output = $this->runSnippet($snippet, [
            self::$datasetId,
        ]);

        $this->assertContains('Data imported successfully', $output);
        $tableId = 'us_states';

        // verify table contents
        $query = sprintf('SELECT * FROM `%s.%s`', self::$datasetId, $tableId);
        $testFunction = function () use ($query) {
            $output = $this->runSnippet('run_query', [$query]);
            $this->assertContains('Washington', $output);
        };

        $this->runEventuallyConsistentTest($testFunction);
        $table = self::$dataset->table($tableId);
        $table->delete();
    }

    public function provideImportFromStorage()
    {
        return [
            ['import_from_storage_csv'],
            ['import_from_storage_json']
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
        $output = $this->runSnippet('insert_sql', [
            self::$datasetId,
            $tmpFile,
        ]);

        $this->assertContains('Data imported successfully', $output);
        $this->verifyTempTable($tempTableId);
    }

    public function testListDatasets()
    {
        $output = $this->runSnippet('list_datasets');
        $this->assertContains(self::$datasetId, $output);
    }

    public function testListTables()
    {
        $tempTableId = $this->createTempEmptyTable();
        $output = $this->runSnippet('list_tables', [self::$datasetId]);
        $this->assertContains($tempTableId, $output);
    }

    public function testStreamRow()
    {
        $tempTableId = $this->createTempEmptyTable();

        // run the import
        $output = $this->runSnippet('stream_row', [
            self::$datasetId,
            $tempTableId,
            json_encode(['name' => 'Brent Shaffer', 'title' => 'Developer'])
        ]);

        $this->assertcontains('Data streamed into BigQuery successfully', $output);
        $this->verifyTempTable($tempTableId);
    }

    public function testPaginateTable()
    {
        $tableId = $this->createTempTable();
        $output = $this->runSnippet('paginate_table', [
            self::$datasetId,
            $tableId,
            1
        ]);
        $this->assertContains('Brent Shaffer', $output);
    }

    public function testRunQuery()
    {
        $query = 'SELECT corpus, COUNT(*) as unique_words
            FROM `publicdata.samples.shakespeare` GROUP BY corpus LIMIT 10';

        $output = $this->runSnippet('run_query', [$query]);
        $this->assertContains('hamlet', $output);
        $this->assertContains('kinglear', $output);
        $this->assertContains('Found 10 row(s)', $output);
    }

    public function testRunQueryAsJob()
    {
        $tableId = $this->createTempTable();
        $query = sprintf(
            'SELECT * FROM `%s.%s` LIMIT 1',
            self::$datasetId,
            $tableId
        );

        $output = $this->runSnippet('run_query_as_job', [$query]);
        $this->assertContains('Found 1 row(s)', $output);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId], $params);
        ob_start();
        require __DIR__ . "/../snippets/$sampleName.php";
        return ob_get_clean();
    }

    private function createTempEmptyTable()
    {
        $tempTableId = sprintf('test_table_%s_%s', time(), rand());
        $this->runSnippet('create_table', [
            self::$datasetId,
            $tempTableId,
            json_encode([
                ['name' => 'name', 'type' => 'string', 'mode' => 'nullable'],
                ['name' => 'title', 'type' => 'string', 'mode' => 'nullable']
            ])
        ]);
        return $tempTableId;
    }

    private function createTempTable()
    {
        $tempTableId = $this->createTempEmptyTable();
        $source = __DIR__ . '/data/test_data.csv';
        $output = $this->runSnippet('import_from_local_csv', [
            self::$datasetId,
            $tempTableId,
            $source,
        ]);
        return $tempTableId;
    }

    private function verifyTempTable($tempTableId)
    {
        $query = sprintf('SELECT * FROM `%s.%s`', self::$datasetId, $tempTableId);
        $testFunction = function () use ($query) {
            $output = $this->runSnippet('run_query', [$query]);
            $this->assertContains('Brent Shaffer', $output);
        };

        $this->runEventuallyConsistentTest($testFunction);
    }

    public static function tearDownAfterClass()
    {
        self::$dataset->delete(['deleteContents' => true]);
    }
}
