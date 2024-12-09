<?php
/**
 * Copyright 2024 Google LLC
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

use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
 */
class spannerBackupScheduleTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    use RetryTrait, EventuallyConsistentTestTrait;

    /** @var string instanceId */
    protected static $instanceId;

    /** @var string backupScheduleId */
    protected static $backupScheduleId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var $instance Instance */
    protected static $instance;

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        if ('true' !== getenv('GOOGLE_SPANNER_RUN_BACKUP_SCHEDULE_TESTS')) {
            self::markTestSkipped('Skipping backup schedule tests.');
        }
        self::$instanceId = self::requireEnv('GOOGLE_SPANNER_INSTANCE_ID');

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$databaseId = 'your-database-id';
        self::$backupScheduleId = 'backup-schedule-' . self::$databaseId;
        self::$instance = $spanner->instance(self::$instanceId);
    }

    /**
     * @test
     */
    public function testCreateBackupSchedule()
    {
        $output = $this->runFunctionSnippet('create_backup_schedule', [
            self::$databaseId,
            self::$backupScheduleId,
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
        $this->assertStringContainsString(self::$instanceId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$backupScheduleId, $output);
    }

    /**
     * @test
     * @depends testCreateBackupSchedule
     */
    public function testGetBackupSchedule()
    {
        $output = $this->runFunctionSnippet('get_backup_schedule', [
            self::$databaseId,
            self::$backupScheduleId,
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
        $this->assertStringContainsString(self::$instanceId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$backupScheduleId, $output);
    }

    /**
     * @test
     * @depends testCreateBackupSchedule
     */
    public function testListBackupSchedules()
    {
        $output = $this->runFunctionSnippet('list_backup_schedules', [
            self::$databaseId,
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
        $this->assertStringContainsString(self::$instanceId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
    }

    /**
     * @test
     * @depends testCreateBackupSchedule
     */
    public function testUpdateBackupSchedule()
    {
        $output = $this->runFunctionSnippet('update_backup_schedule', [
            self::$databaseId,
            self::$backupScheduleId,
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
        $this->assertStringContainsString(self::$instanceId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$backupScheduleId, $output);
    }

    /**
     * @test
     * @depends testCreateBackupSchedule
     */
    public function testDeleteBackupSchedule()
    {
        $output = $this->runFunctionSnippet('delete_backup_schedule', [
            self::$databaseId,
            self::$backupScheduleId,
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
        $this->assertStringContainsString(self::$instanceId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$backupScheduleId, $output);
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_merge([self::$projectId, self::$instanceId], array_values($params))
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$instance->exists()) {
            $backoff = new ExponentialBackoff(3);

            /** @var Database $db */
            foreach (self::$instance->databases() as $db) {
                if (false !== strpos($db->name(), self::$databaseId)) {
                    $backoff->execute(function () use ($db) {
                        $db->drop();
                    });
                }
            }
        }
    }
}
