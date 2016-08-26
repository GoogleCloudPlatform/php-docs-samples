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

use Google\Cloud\Samples\BigQuery\ExportCommand;
use Google\Cloud\ServiceBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ExportCommand.
 */
class ExportCommandTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessage Destination does not contain object name
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

        $application = new Application();
        $application->add(new ExportCommand());
        $commandTester = new CommandTester($application->get('export'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'destination' => 'gs://foo',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
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
        $application->add(new ExportCommand());
        $commandTester = new CommandTester($application->get('export'));
        $commandTester->execute(
            [
                'dataset.table' => 'invalid.table.name',
                'destination' => 'gs://foo/bar',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid format
     */
    public function testInvalidFormatThrowsException()
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
        $application->add(new ExportCommand());
        $commandTester = new CommandTester($application->get('export'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'destination' => 'gs://foo/bar',
                '--format' => 'invalid-format',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Destination must start with "gs://" for Cloud Storage
     */
    public function testInvalidDestinationThrowsException()
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
        $application->add(new ExportCommand());
        $commandTester = new CommandTester($application->get('export'));
        $commandTester->execute(
            [
                'dataset.table' => $datasetId . '.' . $tableId,
                'destination' => 'foo',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    /**
     * @dataProvider provideExport
     */
    public function testExport($objectName, $format)
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
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }
        if (!self::$gcsBucket) {
            $this->markTestSkipped('No Cloud Storage bucket');
        }

        $destination = sprintf('gs://%s/%s', self::$gcsBucket, $objectName);

        // run the import
        $application = new Application();
        $application->add(new ExportCommand());
        $commandTester = new CommandTester($application->get('export'));
        $commandTester->execute([
            'dataset.table' => $datasetId . '.' . $tableId,
            'destination' => $destination,
            '--format' => $format,
            '--project' => $projectId,
        ], ['interactive' => false]);

        $this->expectOutputRegex('/Data exported successfully/');

        // verify the contents of the bucket
        $builder = new ServiceBuilder([
            'projectId' => $projectId,
        ]);
        $storage = $builder->storage();
        $object = $storage->bucket(self::$gcsBucket)->object($objectName);
        $contents = $object->downloadAsString();
        $this->assertContains('Brent Shaffer', $contents);
        $this->assertContains('Takashi Matsuo', $contents);
        $this->assertContains('Jeffrey Rennie', $contents);
        $object->delete();
        $this->assertFalse($object->exists());
    }

    public function provideExport()
    {
        $time = time();

        return [
            [sprintf('test_data_%s.json', $time), 'json'],
            [sprintf('test_data_%s.csv', $time), 'csv'],
        ];
    }
}
