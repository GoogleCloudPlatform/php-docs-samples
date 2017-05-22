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

namespace Google\Cloud\Samples\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class spannerTest extends \PHPUnit_Framework_TestCase
{
    /* @var string instanceId */
    protected static $instanceId;

    /* @var string databaseId */
    protected static $databaseId;

    /* @var $instance Instance */
    protected static $instance;

    /* @var $application Application */
    protected static $application;


    public static function setUpBeforeClass()
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            self::markTestSkipped('No application credentials were found');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            self::markTestSkipped('GOOGLE_PROJECT_ID must be set.');
        }

        $spanner = new SpannerClient([
            'projectId' => $projectId,
        ]);

        self::$instanceId = self::$databaseId = 'test-' . time() . rand();
        $configurationId = "projects/$projectId/instanceConfigs/regional-us-central1";

        $configuration = $spanner->instanceConfiguration($configurationId);
        $instance = $spanner->instance(self::$instanceId);

        $operation = $instance->create($configuration);
        $operation->result();

        self::$instance = $instance;

        self::$application = require __DIR__ . '/../spanner.php';
    }

    public function testCreateDatabase()
    {
        $this->runCommand('create-database');
        $this->expectOutputRegex('/Waiting for operation to complete.../');
        $this->expectOutputRegex('/Created database test-/');
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertData()
    {
        $this->runCommand('insert-data');
        $this->expectOutputRegex('/Inserted data./');
    }

    /**
     * @depends testInsertData
     */
    public function testQueryData()
    {
        $this->runCommand('query-data');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go/');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 1, AlbumTitle: Green/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 3, AlbumTitle: Terrified/');
    }

    /**
     * @depends testInsertData
     */
    public function testReadData()
    {
        $this->runCommand('read-data');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go/');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 1, AlbumTitle: Green/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 3, AlbumTitle: Terrified/');
    }

    /**
     * @depends testInsertData
     */
    public function testCreateIndex()
    {
        $this->runCommand('create-index');
        $this->expectOutputRegex('/Waiting for operation to complete.../');
        $this->expectOutputRegex('/Added the AlbumsByAlbumTitle index./');
    }

    /**
     * @depends testCreateIndex
     */
    public function testQueryDataWithIndex()
    {
        $this->runCommand('query-data-with-index');
        $this->expectOutputRegex('/AlbumId: 2, AlbumTitle: Forever Hold Your Peace/');
        $this->expectOutputRegex('/AlbumId: 1, AlbumTitle: Go, Go, Go/');
    }

    /**
     * @depends testCreateIndex
     */
    public function testReadDataWithIndex()
    {
        $this->runCommand('read-data-with-index');

        $this->expectOutputRegex('/AlbumId: 1, AlbumTitle: Go, Go, Go/');
        $this->expectOutputRegex('/AlbumId: 2, AlbumTitle: Total Junk/');
        $this->expectOutputRegex('/AlbumId: 1, AlbumTitle: Green/');
        $this->expectOutputRegex('/AlbumId: 2, AlbumTitle: Forever Hold Your Peace/');
        $this->expectOutputRegex('/AlbumId: 3, AlbumTitle: Terrified/');
    }

    /**
     * @depends testInsertData
     */
    public function testAddColumn()
    {
        $this->runCommand('add-column');
        $this->expectOutputRegex('/Waiting for operation to complete.../');
        $this->expectOutputRegex('/Added the MarketingBudget column./');
    }

    /**
     * @depends testAddColumn
     */
    public function testQueryDataWithNewColumn()
    {
        $this->runCommand('query-data-with-new-column');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 1, MarketingBudget:/');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 2, MarketingBudget:/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 1, MarketingBudget:/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 2, MarketingBudget:/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 3, MarketingBudget:/');
    }

    /**
     * @depends testAddColumn
     */
    public function testCreateStoringIndex()
    {
        $this->runCommand('create-storing-index');
        $this->expectOutputRegex('/Waiting for operation to complete.../');
        $this->expectOutputRegex('/Added the AlbumsByAlbumTitle2 index./');
    }

    /**
     * @depends testCreateStoringIndex
     */
    public function testReadDataWithStoringIndex()
    {
        $this->runCommand('read-data-with-storing-index');

        $this->expectOutputRegex('/AlbumId: 2, AlbumTitle: Forever Hold Your Peace, MarketingBudget:/');
        $this->expectOutputRegex('/AlbumId: 1, AlbumTitle: Go, Go, Go, MarketingBudget:/');
        $this->expectOutputRegex('/AlbumId: 1, AlbumTitle: Green, MarketingBudget:/');
        $this->expectOutputRegex('/AlbumId: 3, AlbumTitle: Terrified, MarketingBudget:/');
        $this->expectOutputRegex('/AlbumId: 2, AlbumTitle: Total Junk, MarketingBudget:/');
    }

    /**
     * @depends testCreateStoringIndex
     */
    public function testUpdateData()
    {
        $this->runCommand('update-data');
        $this->expectOutputRegex('/Updated data./');
    }

    /**
     * @depends testUpdateData
     */
    public function testReadOnlyTransaction()
    {
        $this->runCommand('read-only-transaction');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go/');
        $this->expectOutputRegex('/SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 1, AlbumTitle: Green/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace/');
        $this->expectOutputRegex('/SingerId: 2, AlbumId: 3, AlbumTitle: Terrified/');
    }

    /**
     * @depends testUpdateData
     */
    public function testReadWriteTransaction()
    {
        $this->runCommand('read-write-transaction');
        $this->expectOutputRegex('/Setting first album\'s budget to 120000 and the second album\'s budget to 480000/');
        $this->expectOutputRegex('/Transaction complete./');
    }

    private function runCommand($commandName)
    {
        $application = require __DIR__ . '/../spanner.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'instance_id' => self::$instanceId,
            'database_id' => self::$databaseId,
        ], [
            'interactive' => false
        ]);
    }

    public static function tearDownAfterClass()
    {
        if (self::$instance && !getenv('GOOGLE_SPANNER_KEEP_INSTANCE')) {
            self::$instance->delete();
        }
    }
}
