<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ImportCommand
 */
class ImportCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected static $gcsBucket;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$gcsBucket = getenv('GOOGLE_BUCKET_NAME');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source file does not exist or is not readable
     */
    public function testNonexistantFileThrowsException()
    {
        $import = new ImportCommand();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $import->importFromFile($table, '/this/file/doesnotexist.json');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source file does not exist or is not readable
     */
    public function testUnreadableFileThrowsException()
    {
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');
        chmod($file, 000);
        $import = new ImportCommand();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $import->importFromFile($table, $file);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source format unknown. Must be JSON or CSV
     */
    public function testFileWithWrongExtensionThrowsException()
    {
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');
        $import = new ImportCommand();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $import->importFromFile($table, $file);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source does not contain object name
     */
    public function testBucketWithoutObjectThrowsException()
    {
        $import = new ImportCommand();
        $storage = $this->getMockBuilder('Google\Cloud\Storage\StorageClient')
            ->disableOriginalConstructor()
            ->getMock();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $import->importFromCloudStorage($table, $storage, 'gs://foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Table must in the format "dataset.table"
     */
    public function testInvalidTableNameThrowsException()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute(
            [
                'dataset.table' => 'invalid.table.name',
                'source' => 'foo',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    public function testImportFromFileWithJson()
    {
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');
        rename($file, $file .= '.json');
        $import = new ImportCommand();
        $job = $this->getMockBuilder('Google\Cloud\BigQuery\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('load')
            ->with(
                $this->isType('resource'),
                [ 'jobConfig' => [ 'sourceFormat' => 'NEWLINE_DELIMITED_JSON' ]]
            )
            ->will($this->returnValue($job));
        $result = $import->importFromFile($table, $file);
        $this->assertInstanceOf('Google\Cloud\BigQuery\Job', $result);
    }

    public function testImportFromFileWithCsv()
    {
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');
        rename($file, $file .= '.csv');
        $import = new ImportCommand();
        $job = $this->getMockBuilder('Google\Cloud\BigQuery\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('load')
            ->with(
                $this->isType('resource'),
                [ 'jobConfig' => [ 'sourceFormat' => 'CSV' ]]
            )
            ->will($this->returnValue($job));
        $result = $import->importFromFile($table, $file);
        $this->assertInstanceOf('Google\Cloud\BigQuery\Job', $result);
    }

    public function testImportFromCloudStorage()
    {
        $import = new ImportCommand();
        $object = $this->getMockBuilder('Google\Cloud\Storage\Object')
            ->disableOriginalConstructor()
            ->getMock();
        $bucket = $this->getMockBuilder('Google\Cloud\Storage\Bucket')
            ->disableOriginalConstructor()
            ->getMock();
        $bucket->expects($this->once())
            ->method('object')
            ->with('bar')
            ->will($this->returnValue($object));
        $storage = $this->getMockBuilder('Google\Cloud\Storage\StorageClient')
            ->disableOriginalConstructor()
            ->getMock();
        $storage->expects($this->once())
            ->method('bucket')
            ->with('foo')
            ->will($this->returnValue($bucket));
        $job = $this->getMockBuilder('Google\Cloud\BigQuery\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('loadFromStorage')
            ->with(
                $object,
                [ ]
            )
            ->will($this->returnValue($job));
        $result = $import->importFromCloudStorage($table, $storage, 'gs://foo/bar');
        $this->assertInstanceOf('Google\Cloud\BigQuery\Job', $result);
    }

    public function testImportDatastoreBackupFromCloudStorage()
    {
        $import = new ImportCommand();
        $object = $this->getMockBuilder('Google\Cloud\Storage\Object')
            ->disableOriginalConstructor()
            ->getMock();
        $bucket = $this->getMockBuilder('Google\Cloud\Storage\Bucket')
            ->disableOriginalConstructor()
            ->getMock();
        $bucket->expects($this->once())
            ->method('object')
            ->with('bar.backup_info')
            ->will($this->returnValue($object));
        $storage = $this->getMockBuilder('Google\Cloud\Storage\StorageClient')
            ->disableOriginalConstructor()
            ->getMock();
        $storage->expects($this->once())
            ->method('bucket')
            ->with('foo')
            ->will($this->returnValue($bucket));
        $job = $this->getMockBuilder('Google\Cloud\BigQuery\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('loadFromStorage')
            ->with(
                $object,
                [ 'jobConfig' => [ 'sourceFormat' => 'DATASTORE_BACKUP' ]]
            )
            ->will($this->returnValue($job));
        $result = $import->importFromCloudStorage($table, $storage, 'gs://foo/bar.backup_info');
        $this->assertInstanceOf('Google\Cloud\BigQuery\Job', $result);
    }

    public function testStreamRow()
    {
        $data = ['name' => 'Brent Shaffer', 'title' => 'PHP Developer'];
        $import = new ImportCommand();
        $insertResponse = $this->getMockBuilder('Google\Cloud\BigQuery\InsertResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $insertResponse->expects($this->exactly(2))
            ->method('isSuccessful')
            ->will($this->returnValue(true));
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('insertRows')
            ->with([
                ['insertId' => '123', 'data' => $data]
            ])
            ->will($this->returnValue($insertResponse));
        $result = $import->streamRow($table, $data, '123');
        $this->assertTrue($result);
    }

    public function testStreamRowWithErrors()
    {
        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $output->expects($this->once())
            ->method('write')
            ->with('invalid: Missing required field: title.' . PHP_EOL);
        $GLOBALS['output'] = $output;
        $data = ['name' => 'Brent Shaffer', 'title' => null];
        $import = new ImportCommand();
        $insertResponse = $this->getMockBuilder('Google\Cloud\BigQuery\InsertResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $insertResponse->expects($this->exactly(2))
            ->method('isSuccessful')
            ->will($this->returnValue(false));
        $insertResponse->expects($this->once())
            ->method('failedRows')
            ->will($this->returnValue([
                [
                    'errors' => [
                        ['reason' => 'invalid', 'message' => 'Missing required field: title.']
                    ]
                ]
            ]));
        $table = $this->getMockBuilder('Google\Cloud\BigQuery\Table')
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->once())
            ->method('insertRows')
            ->with([
                ['insertId' => null, 'data' => $data]
            ])
            ->will($this->returnValue($insertResponse));
        $result = $import->streamRow($table, $data);
        $this->assertFalse($result);
    }

    public function testImportStreamRow()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }

        $tableId = sprintf('test_table_%s', time());
        $this->createTempTable($projectId, $datasetId, $tableId);

        $questionHelper = $this->getMockBuilder('Symfony\Component\Console\Helper\QuestionHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $questionHelper->expects($this->exactly(2))
            ->method('ask')
            ->will($this->onConsecutiveCalls('Brent Shaffer', 'PHP Developer'));
        $helperSet = $this->getMockBuilder('Symfony\Component\Console\Helper\HelperSet')
            ->disableOriginalConstructor()
            ->getMock();
        $helperSet->expects($this->once())
            ->method('get')
            ->with('question')
            ->will($this->returnValue($questionHelper));

        // run the import
        $application = new Application();
        $application->add($import = new ImportCommand());
        $import->setHelperSet($helperSet);
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute([
            'dataset.table' => $datasetId . '.' . $tableId,
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->assertContains('Data streamed into BigQuery successfully', $commandTester->getDisplay());

        sleep(1); // streaming doesn't use jobs, so we need this to ensure the data
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute([
            'query' => sprintf('SELECT * FROM [%s.%s]', $datasetId, $tableId),
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->deleteTempTable($projectId, $datasetId, $tableId);

        $this->assertContains('Brent Shaffer', $commandTester->getDisplay());
    }

    /**
     * @dataProvider provideImport
     */
    public function testImport($source, $createTable = true)
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }

        if (0 === strpos($source, 'gs://') && !self::$gcsBucket) {
            $this->markTestSkipped('No Cloud Storage bucket');
        }

        $tableId = sprintf('test_table_%s', time());
        if ($createTable) {
            $this->createTempTable($projectId, $datasetId, $tableId);
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute([
            'dataset.table' => $datasetId . '.' . $tableId,
            'source' => $source,
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->assertContains('Data imported successfully', $commandTester->getDisplay());

        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute([
            'query' => sprintf('SELECT * FROM [%s.%s]', $datasetId, $tableId),
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->deleteTempTable($projectId, $datasetId, $tableId);

        $this->assertContains('Brent Shaffer', $commandTester->getDisplay());
        $this->assertContains('Takashi Matsuo', $commandTester->getDisplay());
        $this->assertContains('Jeffrey Rennie', $commandTester->getDisplay());
    }

    public function provideImport()
    {
        $bucket = getenv('GOOGLE_BUCKET_NAME');
        return [
            [__DIR__ . '/data/test_data.csv'],
            [__DIR__ . '/data/test_data.json'],
            [sprintf('gs://%s/test_data.csv', $bucket)],
            [sprintf('gs://%s/test_data.json', $bucket)],
            [sprintf('gs://%s/test_data.backup_info', $bucket), false],
        ];
    }

    private function createTempTable($projectId, $datasetId, $tableId)
    {
        $schema = [
            ['name' => 'name', 'type' => 'string', 'mode' => 'required'],
            ['name' => 'title', 'type' => 'string', 'mode' => 'required'],
        ];
        $schemaJson = tempnam(sys_get_temp_dir(), 'schema-');
        file_put_contents($schemaJson, json_encode($schema));

        $application = new Application();
        $application->add(new SchemaCommand());

        // create the tmp table using the schema command
        $commandTester = new CommandTester($application->get('schema'));
        $commandTester->execute([
            'dataset.table' => $datasetId . '.' . $tableId,
            'schema-json' => $schemaJson,
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->assertContains('Table created successfully', $commandTester->getDisplay());
    }

    private function deleteTempTable($projectId, $datasetId, $tableId)
    {
        $application = new Application();
        $application->add(new SchemaCommand());

        // create the tmp table using the schema command
        $commandTester = new CommandTester($application->get('schema'));
        $commandTester->execute([
            'dataset.table' => $datasetId . '.' . $tableId,
            '--delete' => true,
            '--no-confirmation' => true,
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->assertContains('Table deleted successfully', $commandTester->getDisplay());
    }
}
