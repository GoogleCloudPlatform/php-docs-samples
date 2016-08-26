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

use Google\Cloud\Samples\BigQuery;
use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ImportCommand.
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source file does not exist or is not readable
     */
    public function testNonexistantFileThrowsException()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'source' => '/this/file/doesnotexist.json',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source format unknown. Must be JSON or CSV
     */
    public function testFileWithWrongExtensionThrowsException()
    {
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'source' => $file,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Source does not contain object name
     */
    public function testBucketWithoutObjectThrowsException()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'source' => 'gs://',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
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

        $this->expectOutputRegex('/Data streamed into BigQuery successfully/');

        sleep(1); // streaming doesn't use jobs, so we need this to ensure the data
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute([
            'query' => sprintf('SELECT * FROM [%s.%s]', $datasetId, $tableId),
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->deleteTempTable($projectId, $datasetId, $tableId);

        $this->expectOutputRegex('/Brent Shaffer/');
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

        $this->expectOutputRegex('/Data imported successfully/');

        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute([
            'query' => sprintf('SELECT * FROM [%s.%s]', $datasetId, $tableId),
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->deleteTempTable($projectId, $datasetId, $tableId);

        $this->expectOutputRegex('/Brent Shaffer/');
        $this->expectOutputRegex('/Takashi Matsuo/');
        $this->expectOutputRegex('/Jeffrey Rennie/');
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

        $this->expectOutputRegex('/Table created successfully/');
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

        $this->expectOutputRegex('/Table deleted successfully/');
    }

    private function getMockServiceBuilder($table, $storage = null)
    {
        $dataset = $this->getMockBuilder('Google\Cloud\BigQuery\Dataset')
            ->disableOriginalConstructor()
            ->getMock();
        $dataset->expects($this->once())
            ->method('table')
            ->will($this->returnValue($table));
        $bigQuery = $this->getMockBuilder('Google\Cloud\BigQuery\BigQueryClient')
            ->disableOriginalConstructor()
            ->getMock();
        $bigQuery->expects($this->once())
            ->method('dataset')
            ->will($this->returnValue($dataset));
        $builder = $this->getMockBuilder('Google\Cloud\ServiceBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('bigQuery')
            ->will($this->returnValue($bigQuery));

        if ($storage) {
            $builder->expects($this->once())
                ->method('storage')
                ->will($this->returnValue($storage));
        }

        return $builder;
    }
}
