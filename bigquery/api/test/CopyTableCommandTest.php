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

use Google\Cloud\Samples\BigQuery\CopyTableCommand;
use Google\Cloud\Samples\BigQuery\QueryCommand;
use Google\Cloud\Samples\BigQuery\SchemaCommand;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for CopyTableCommand.
 */
class CopyTableCommandTest extends TestCase
{
    use EventuallyConsistentTestTrait;

    protected static $hasCredentials;
    private static $projectId;
    private $tempTableId;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$projectId = getenv('GOOGLE_PROJECT_ID');
    }

    public function testCopyTable()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!self::$projectId) {
            $this->markTestSkipped('No project ID');
        }
        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$sourceTableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }

        $destinationTableId = sprintf('test_copy_table_%s', time());

        // run the import
        $application = new Application();
        $application->add(new CopyTableCommand());
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('copy-table'));
        $commandTester->execute([
            'dataset' => $datasetId,
            'source-table' => $sourceTableId,
            'destination-table' => $destinationTableId,
            '--project' => self::$projectId,
        ], ['interactive' => false]);

        $this->tempTableId = $datasetId . '.' . $destinationTableId;
        $this->expectOutputRegex('/Table copied successfully/');

        $commandTester = new CommandTester($application->get('query'));
        $testFunction = function () use ($commandTester, $datasetId, $destinationTableId) {
            ob_start();
            $commandTester->execute([
                'query' => sprintf('SELECT * FROM `%s.%s`', $datasetId, $destinationTableId),
                '--project' => self::$projectId,
            ], ['interactive' => false]);
            $output = ob_get_clean();
            $this->assertContains('Brent Shaffer', $output);
            $this->assertContains('Takashi Matsuo', $output);
            $this->assertContains('Jeffrey Rennie', $output);
        };

        $this->runEventuallyConsistentTest($testFunction);
    }

    protected function tearDown()
    {
        if ($this->tempTableId) {
            $application = new Application();
            $application->add(new SchemaCommand());

            // create the tmp table using the schema command
            $commandTester = new CommandTester($application->get('schema'));
            $commandTester->execute([
                'dataset.table' => $this->tempTableId,
                '--delete' => true,
                '--no-confirmation' => true,
                '--project' => self::$projectId,
            ], ['interactive' => false]);
        }
    }
}
