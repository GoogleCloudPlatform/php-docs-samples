<?php

/**
 * Copyright 2019 Google LLC.
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

namespace Google\Cloud\Samples\Bigtable\Tests;

use Exception;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

trait BigtableTestTrait
{
    use TestTrait;
    use ExponentialBackoffTrait;

    private static $instanceAdminClient;
    private static $tableAdminClient;
    private static $bigtableClient;
    private static $instanceId;
    private static $tableId;

    public static function setUpBigtableVars()
    {
        self::checkProjectEnvVarBeforeClass();
        self::$instanceAdminClient = new BigtableInstanceAdminClient();
        self::$tableAdminClient = new BigtableTableAdminClient();
        self::$bigtableClient = new BigtableClient([
            'projectId' => self::$projectId,
        ]);
    }

    public static function createDevInstance($instanceIdPrefix)
    {
        $instanceId = uniqid($instanceIdPrefix);
        $output = self::runSnippet('create_dev_instance', [
            self::$projectId,
            $instanceId,
            $instanceId,
        ]);

        // Verify the instance was created successfully
        if (false !== strpos($output, 'Error: ')) {
            throw new Exception('Error creating instance: ' . $output);
        }

        return $instanceId;
    }

    public static function createTable($tableIdPrefix, $columns = [])
    {
        $tableId = uniqid($tableIdPrefix);

        $formattedParent = self::$tableAdminClient
            ->instanceName(self::$projectId, self::$instanceId);

        $columns = $columns ?: ['stats_summary'];
        $table = (new Table())->setColumnFamilies(array_combine(
                $columns,
                array_fill(0, count($columns), new ColumnFamily)
        ));

        self::$tableAdminClient->createtable(
            $formattedParent,
            $tableId,
            $table
        );

        return $tableId;
    }

    public static function deleteBigtableInstance()
    {
        $instanceName = self::$instanceAdminClient->instanceName(
            self::$projectId,
            self::$instanceId
        );
        self::$instanceAdminClient->deleteInstance($instanceName);
    }

    private static function runSnippet($sampleName, $params = [])
    {
        $sampleFile = sprintf('%s/../src/%s.php', __DIR__, $sampleName);

        $testFunc = function () use ($sampleFile, $params) {
            return shell_exec(sprintf(
                'php %s %s',
                $sampleFile,
                implode(' ', array_map('escapeshellarg', $params))
            ));
        };

        if (isset(self::$backoff)) {
            return self::$backoff->execute($testFunc);
        }
        return $testFunc();
    }
}
