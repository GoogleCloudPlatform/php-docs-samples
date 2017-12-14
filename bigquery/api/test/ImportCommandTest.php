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

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Cloud\Samples\BigQuery\ImportCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ImportCommand.
 */
class ImportCommandTest extends \PHPUnit_Framework_TestCase
{
    use EventuallyConsistentTestTrait;

    protected static $hasCredentials;
    private $gcsBucket;
    private $projectId;
    private $datasetId;
    private $tempTableId;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $this->gcsBucket = getenv('GOOGLE_BUCKET_NAME');
        $this->projectId = getenv('GOOGLE_PROJECT_ID');
        $this->datasetId = getenv('GOOGLE_BIGQUERY_DATASET');
    }

    public function tearDown()
    {
        if ($this->tempTableId) {
            $this->deleteTempTable($this->projectId, $this->datasetId, $this->tempTableId);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Table must in the format "dataset.table"
     */
    public function testInvalidTableNameThrowsException()
    {
        if (!$this->projectId) {
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
                '--project' => $this->projectId,
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
        if (!$this->projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$this->datasetId) {
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
                'dataset.table' => $this->datasetId . '.' . $tableId,
                'source' => '/this/file/doesnotexist.json',
                '--project' => $this->projectId,
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
        if (!$this->projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$this->datasetId) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }
        $file = tempnam(sys_get_temp_dir(), 'bigquery-source');

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute(
            [
                'dataset.table' => $this->datasetId . '.' . $tableId,
                'source' => $file,
                '--project' => $this->projectId,
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
        if (!$this->projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$this->datasetId) {
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
                'dataset.table' => $this->datasetId . '.' . $tableId,
                'source' => 'gs://',
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );
    }

    public function testImportStreamRow()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$this->projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$this->datasetId) {
            $this->markTestSkipped('No bigquery dataset name');
        }

        $tableId = sprintf('test_table_%s', time());
        $this->createTempTable($this->projectId, $this->datasetId, $tableId);

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
        $application->add(new QueryCommand());
        $application->add($import = new ImportCommand());
        $import->setHelperSet($helperSet);
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute([
            'dataset.table' => $this->datasetId . '.' . $tableId,
            '--project' => $this->projectId,
        ], ['interactive' => false]);

        $this->expectOutputRegex('/Data streamed into BigQuery successfully/');

        $commandTester = new CommandTester($application->get('query'));
        $testFunction = function () use ($commandTester, $tableId) {
            ob_start();
            $commandTester->execute([
                'query' => sprintf('SELECT * FROM `%s.%s`', $this->datasetId, $tableId),
                '--project' => $this->projectId,
            ], ['interactive' => false]);
            $output = ob_get_clean();
            $this->assertContains('Brent Shaffer', $output);
        };

        $this->runEventuallyConsistentTest($testFunction);

        $this->tempTableId = $tableId;
    }

    /**
     * @dataProvider provideImport
     */
    public function testImport($source, $createTable = true)
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$this->projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$this->datasetId) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (0 === strpos($source, 'gs://') && !$this->gcsBucket) {
            $this->markTestSkipped('No Cloud Storage bucket');
        }
        $tableId = sprintf('test_table_%s', time());
        if ($createTable) {
            $this->createTempTable($this->projectId, $this->datasetId, $tableId);
        }
        if ('sql' === substr($source, -3)) {
            $contents = file_get_contents($source);
            $contents = str_replace('test_table', $tableId, $contents);
            $source = sprintf('%s/%s.sql', sys_get_temp_dir(), $tableId);
            file_put_contents($source, $contents);
        }

        // run the import
        $application = new Application();
        $application->add(new ImportCommand());
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('import'));
        $commandTester->execute([
            'dataset.table' => $this->datasetId . '.' . $tableId,
            'source' => $source,
            '--project' => $this->projectId,
        ], ['interactive' => false]);

        $this->expectOutputRegex('/Data imported successfully/');

        $commandTester = new CommandTester($application->get('query'));
        $testFunction = function () use ($commandTester, $tableId) {
            ob_start();
            $commandTester->execute([
                'query' => sprintf('SELECT * FROM [%s.%s]', $this->datasetId, $tableId),
                '--project' => $this->projectId,
            ], ['interactive' => false]);
            $output = ob_get_clean();
            $this->assertContains('Brent Shaffer', $output);
            $this->assertContains('Takashi Matsuo', $output);
            $this->assertContains('Jeffrey Rennie', $output);
        };

        $this->runEventuallyConsistentTest($testFunction);

        $this->tempTableId = $tableId;
    }

    public function provideImport()
    {
        $bucket = getenv('GOOGLE_BUCKET_NAME');

        return [
            [__DIR__ . '/data/test_data.csv'],
            [__DIR__ . '/data/test_data.json'],
            [__DIR__ . '/data/test_data.sql'],
            [sprintf('gs://%s/test_data.csv', $bucket)],
            [sprintf('gs://%s/test_data.json', $bucket)],
            [sprintf('gs://%s/test_data.backup_info', $bucket), false],
        ];
    }

    private function createTempTable($projectId, $datasetId, $tableId)
    {
        $schema = [
            ['name' => 'name', 'type' => 'string', 'mode' => 'nullable'],
            ['name' => 'title', 'type' => 'string', 'mode' => 'nullable'],
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
    }
}
