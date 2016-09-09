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

use Google\Cloud\Samples\BigQuery\QueryCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for QueryCommand.
 */
class QueryCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function testPublicQuery()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        $query = 'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
            'FROM [publicdata:samples.shakespeare]';

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            ['query' => $query, '--project' => $projectId],
            ['interactive' => false]
        );

        // Make sure it looks like Shakespeare.
        $this->expectOutputRegex('/hamlet/');
        $this->expectOutputRegex('/kinglear/');
        $this->expectOutputRegex('/Found 10 row\(s\)/');
    }

    public function testQueryWithNoResults()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        $query = 'SELECT * FROM [publicdata:samples.shakespeare] LIMIT 0';

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            ['query' => $query, '--project' => $projectId],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Found 0 row\(s\)/');
    }

    public function testQuery()
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

        $query = sprintf('SELECT * FROM [%s.%s] LIMIT 1', $datasetId, $tableId);

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            ['query' => $query, '--project' => $projectId, '--sync'],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Found 1 row\(s\)/');
    }

    public function testQueryStandardSql()
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

        $query = sprintf('SELECT * FROM `%s.%s` LIMIT 1', $datasetId, $tableId);

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            [
              'query' => $query,
              '--project' => $projectId,
              '--sync',
              '--standard-sql' => true
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Found 1 row\(s\)/');
    }

    public function testQueryAsJob()
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

        $query = sprintf('SELECT * FROM [%s.%s] LIMIT 1', $datasetId, $tableId);

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            ['query' => $query, '--project' => $projectId],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Found 1 row\(s\)/');
    }

    public function testQueryAsJobStandardSql()
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

        $query = sprintf('SELECT * FROM `%s.%s` LIMIT 1', $datasetId, $tableId);

        $application = new Application();
        $application->add(new QueryCommand());
        $commandTester = new CommandTester($application->get('query'));
        $commandTester->execute(
            [
                'query' => $query,
                '--project' => $projectId,
                '--standard-sql' => true
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Found 1 row\(s\)/');
    }
}
