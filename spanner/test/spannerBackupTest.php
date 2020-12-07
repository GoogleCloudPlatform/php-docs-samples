<?php
/**
 * Copyright 2020 Google LLC
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
use Google\Cloud\Spanner\Backup;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 */
class spannerBackupTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    use RetryTrait, EventuallyConsistentTestTrait;

    /** @var string instanceId */
    protected static $instanceId;

    /** @var string backupId */
    protected static $backupId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var string restoredDatabaseId */
    protected static $restoredDatabaseId;

    /** @var $instance Instance */
    protected static $instance;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        self::$instanceId = self::requireEnv('GOOGLE_SPANNER_INSTANCE_ID');

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$databaseId = 'test-' . time() . rand();
        self::$backupId = 'backup-' . self::$databaseId;
        self::$restoredDatabaseId = self::$databaseId . '-res';
        self::$instance = $spanner->instance(self::$instanceId);
        self::$instance->database(self::$databaseId)->create();
    }

    public function testCancelBackup()
    {
        $output = $this->runFunctionSnippet('cancel_backup', [
            self::$databaseId
        ]);
        $this->assertContains('Cancel backup operation complete', $output);
    }

    public function testCreateBackup()
    {
        $output = $this->runFunctionSnippet('create_backup', [
            self::$databaseId,
            self::$backupId,
        ]);
        $this->assertContains(self::$backupId, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testListBackupOperations()
    {
        $this->markTestSkipped(
            "See: https://github.com/GoogleCloudPlatform/php-docs-samples/issues/1186"
        );

        $databaseId2 = self::$databaseId . '-2';
        $database2 = self::$instance->database($databaseId2);
        // DB may already exist if the test timed out and retried
        if (!$database2->exists()) {
            $database2->create();
        }
        $backup = self::$instance->backup(self::$backupId . '-pro');
        $lro = $backup->create($databaseId2, new \DateTime('+7 hours'));
        $output = $this->runFunctionSnippet('list_backup_operations', [
            'database_id' => self::$databaseId,
        ]);
        $lro->pollUntilComplete();

        $this->assertContains(basename($backup->name()), $output);
        $this->assertContains($databaseId2, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testListBackups()
    {
        $output = $this->runFunctionSnippet('list_backups');
        $this->assertContains(self::$backupId, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testUpdateBackup()
    {
        $output = $this->runFunctionSnippet('update_backup', [self::$backupId]);
        $this->assertContains(self::$backupId, $output);
    }

    /**
     * @depends testUpdateBackup
     */
    public function testRestoreBackup()
    {
        $output = $this->runFunctionSnippet('restore_backup', [
            self::$restoredDatabaseId,
            self::$backupId,
        ]);
        $this->assertContains(self::$backupId, $output);
        $this->assertContains(self::$databaseId, $output);
    }


    /**
     * @depends testRestoreBackup
     */
    public function testListDatabaseOperations()
    {
        $output = $this->runFunctionSnippet('list_database_operations');
        $this->assertContains(self::$restoredDatabaseId, $output);
    }

    /**
     * @depends testListBackups
     */
    public function testDeleteBackup()
    {
        self::waitForOperations();
        $output = $this->runFunctionSnippet('delete_backup', [
            'backup_id' => self::$backupId,
        ]);
        $this->assertContains(self::$backupId, $output);
    }

    private static function waitForOperations()
    {
        //  Wait for backup operations
        $filter = '(metadata.@type:type.googleapis.com/' .
            'google.spanner.admin.database.v1.%s)';

        $backupOperations = self::$instance->backupOperations([
            'filter' => sprintf($filter, 'CreateBackupMetadata')
        ]);

        $dbOperations = self::$instance->databaseOperations([
            'filter' => sprintf($filter, 'OptimizeRestoredDatabaseMetadata')
        ]);

        foreach ($backupOperations as $operation) {
            if (!$operation->done()) {
                $operation->pollUntilComplete();
            }
        }
        foreach ($dbOperations as $operation) {
            if (!$operation->done()) {
                $operation->pollUntilComplete();
            }
        }
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_merge([self::$instanceId], $params)
        );
    }

    public static function tearDownAfterClass()
    {
        if (self::$instance->exists()) {
            self::waitForOperations();

            $backoff = new ExponentialBackoff(3);

            /** @var Database $db */
            foreach (self::$instance->databases() as $db) {
                if (false !== strpos($db->name(), self::$databaseId)) {
                    $backoff->execute(function () use ($db) {
                        $db->drop();
                    });
                }
            }

            /** @var Backup $backup */
            foreach (self::$instance->backups() as $backup) {
                if (false !== strpos($backup->name(), self::$databaseId)) {
                    $backoff->execute(function () use ($backup) {
                        $backup->delete();
                    });
                }
            }
        }
    }
}
