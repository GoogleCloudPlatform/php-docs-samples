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
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
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

    /** @var string encryptedBackupId */
    protected static $encryptedBackupId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var string retentionPeriod */
    protected static $retentionPeriod;

    /** @var string restoredDatabaseId */
    protected static $restoredDatabaseId;

    /** @var string encryptedRestoredDatabaseId */
    protected static $encryptedRestoredDatabaseId;

    /** @var $instance Instance */
    protected static $instance;

    /** @var string kmsKeyName */
    protected static $kmsKeyName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        self::$instanceId = self::requireEnv('GOOGLE_SPANNER_INSTANCE_ID');

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$retentionPeriod = '7d';
        self::$databaseId = 'test-' . time() . rand();
        self::$backupId = 'backup-' . self::$databaseId;
        self::$encryptedBackupId = 'en-backup-' . self::$databaseId;
        self::$restoredDatabaseId = self::$databaseId . '-r';
        self::$encryptedRestoredDatabaseId = self::$databaseId . '-en-r';
        self::$instance = $spanner->instance(self::$instanceId);

        self::$kmsKeyName =
            'projects/' . self::$projectId . '/locations/us-central1/keyRings/spanner-test-keyring/cryptoKeys/spanner-test-cmek';
    }

    public function testCreateDatabaseWithVersionRetentionPeriod()
    {
        $output = $this->runFunctionSnippet('create_database_with_version_retention_period', [
            self::$databaseId,
            self::$retentionPeriod,
        ]);
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$retentionPeriod, $output);
    }

    public function testCreateBackupWithEncryptionKey()
    {
        $database = self::$instance->database(self::$databaseId);

        $output = $this->runFunctionSnippet('create_backup_with_encryption_key', [
            self::$databaseId,
            self::$encryptedBackupId,
            self::$kmsKeyName,
        ]);
        $this->assertStringContainsString(self::$backupId, $output);
    }

    /**
     * @depends testCreateDatabaseWithVersionRetentionPeriod
     */
    public function testCancelBackup()
    {
        $output = $this->runFunctionSnippet('cancel_backup', [
            self::$databaseId
        ]);
        $this->assertStringContainsString('Cancel backup operation complete', $output);
    }

    /**
     * @depends testCreateDatabaseWithVersionRetentionPeriod
     */
    public function testCreateBackup()
    {
        $database = self::$instance->database(self::$databaseId);
        $results = $database->execute('SELECT TIMESTAMP_TRUNC(CURRENT_TIMESTAMP(), MICROSECOND) as Timestamp');
        $row = $results->rows()->current();
        $versionTime = $row['Timestamp'];

        $output = $this->runFunctionSnippet('create_backup', [
            self::$databaseId,
            self::$backupId,
            $versionTime,
        ]);
        $this->assertStringContainsString(self::$backupId, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testListBackupOperations()
    {
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

        $this->assertStringContainsString(basename($backup->name()), $output);
        $this->assertStringContainsString($databaseId2, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testCopyBackup()
    {
        $newBackupId = 'copy-' . self::$backupId . '-' . time();

        $output = $this->runFunctionSnippet('copy_backup', [
            $newBackupId,
            self::$instanceId,
            self::$backupId
        ]);

        $regex = '/Backup %s of size \d+ bytes was copied at (.+) from the source backup %s/';
        $this->assertRegExp(sprintf($regex, $newBackupId, self::$backupId), $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testListBackups()
    {
        $output = $this->runFunctionSnippet('list_backups');
        $this->assertStringContainsString(self::$backupId, $output);
    }

    /**
     * @depends testCreateBackup
     */
    public function testUpdateBackup()
    {
        $output = $this->runFunctionSnippet('update_backup', [self::$backupId]);
        $this->assertStringContainsString(self::$backupId, $output);
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
        $this->assertStringContainsString(self::$backupId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
    }

    /**
     * @depends testCreateBackupWithEncryptionKey
     */
    public function testRestoreBackupWithEncryptionKey()
    {
        $output = $this->runFunctionSnippet('restore_backup_with_encryption_key', [
            self::$encryptedRestoredDatabaseId,
            self::$encryptedBackupId,
            self::$kmsKeyName,
        ]);
        $this->assertStringContainsString(self::$backupId, $output);
        $this->assertStringContainsString(self::$databaseId, $output);
    }

    /**
     * @depends testRestoreBackupWithEncryptionKey
     */
    public function testListDatabaseOperations()
    {
        $output = $this->runFunctionSnippet('list_database_operations');
        $this->assertStringContainsString(self::$encryptedRestoredDatabaseId, $output);
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
        $this->assertStringContainsString(self::$backupId, $output);
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
            array_merge([self::$instanceId], array_values($params))
        );
    }

    public static function tearDownAfterClass(): void
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
