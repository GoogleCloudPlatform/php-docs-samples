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
    /** @var string instanceId */
    protected static $instanceId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var $instance Instance */
    protected static $instance;

    /** @var $lastUpdateData int */
    protected static $lastUpdateDataTimestamp;

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
        $operation->pollUntilComplete();

        self::$instance = $instance;
    }

    public function testCreateDatabase()
    {
        $output = $this->runCommand('create-database');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Created database test-', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertData()
    {
        $output = $this->runCommand('insert-data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertData
     */
    public function testQueryData()
    {
        $output = $this->runCommand('query-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testReadData()
    {
        $output = $this->runCommand('read-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testAddColumn()
    {
        $output = $this->runCommand('add-column');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the MarketingBudget column.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateData()
    {
        $output = $this->runCommand('update-data');
        self::$lastUpdateDataTimestamp = time();
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testQueryDataWithNewColumn()
    {
        $output = $this->runCommand('query-data-with-new-column');
        $this->assertContains('SingerId: 1, AlbumId: 1, MarketingBudget:', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadWriteTransaction()
    {
        $output = $this->runCommand('read-write-transaction');
        $this->assertContains('Setting first album\'s budget to 300000 and the second album\'s budget to 300000', $output);
        $this->assertContains('Transaction complete.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testCreateIndex()
    {
        $output = $this->runCommand('create-index');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the AlbumsByAlbumTitle index.', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testQueryDataWithIndex()
    {
        $output = $this->runCommand('query-data-with-index');
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testReadDataWithIndex()
    {
        $output = $this->runCommand('read-data-with-index');

        $this->assertContains('AlbumId: 2, AlbumTitle: Total Junk', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('AlbumId: 3, AlbumTitle: Terrified', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testCreateStoringIndex()
    {
        $output = $this->runCommand('create-storing-index');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the AlbumsByAlbumTitle2 index.', $output);
    }

    /**
     * @depends testCreateStoringIndex
     */
    public function testReadDataWithStoringIndex()
    {
        $output = $this->runCommand('read-data-with-storing-index');
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Go, Go, Go, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Green, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 3, AlbumTitle: Terrified, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Total Junk, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadOnlyTransaction()
    {
        $output = $this->runCommand('read-only-transaction');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadStaleData()
    {
        // read-stale-data reads data that is exactly 10 seconds old.  So, make sure 10 seconds
        // have elapsed since testUpdateData().
        $elapsed = time() - self::$lastUpdateDataTimestamp;
        if ($elapsed < 11) {
            sleep(11 - $elapsed);
        }
        $output = $this->runCommand('read-stale-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    private function runCommand($commandName)
    {
        $application = require __DIR__ . '/../spanner.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            'instance_id' => self::$instanceId,
            'database_id' => self::$databaseId,
        ], [
            'interactive' => false
        ]);

        return ob_get_clean();
    }

    public static function tearDownAfterClass()
    {
        if (self::$instance && !getenv('GOOGLE_SPANNER_KEEP_INSTANCE')) {
            self::$instance->delete();
        }
    }
}
