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
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

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
        $output = self::runFunctionSnippet('create_dev_instance', [
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

    public static function createServiceAccount($serviceAccountId)
    {
        // TODO: When this method is exposed in googleapis/google-cloud-php, remove the use of the following
        $scopes = ['https://www.googleapis.com/auth/cloud-platform'];

        // create middleware
        $middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://iam.googleapis.com',
            'auth' => 'google_auth'  // authorize all requests
        ]);

        // make the request
        $response = $client->post('/v1/projects/' . self::$projectId . '/serviceAccounts', [
            'json' => [
                'accountId' => $serviceAccountId,
                'serviceAccount' => [
                    'displayName' => 'Test Service Account',
                    'description' => 'This account should be deleted automatically after the unit tests complete.'
                ]
            ]
        ]);

        return json_decode($response->getBody())->email;
    }

    public static function deleteServiceAccount($serviceAccountEmail)
    {
        // TODO: When this method is exposed in googleapis/google-cloud-php, remove the use of the following
        $scopes = ['https://www.googleapis.com/auth/cloud-platform'];

        // create middleware
        $middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://iam.googleapis.com',
            'auth' => 'google_auth'  // authorize all requests
        ]);

        // make the request
        $client->delete('/v1/projects/' . self::$projectId . '/serviceAccounts/' . $serviceAccountEmail);
    }

    public static function deleteBigtableInstance()
    {
        $instanceName = self::$instanceAdminClient->instanceName(
            self::$projectId,
            self::$instanceId
        );
        self::$instanceAdminClient->deleteInstance($instanceName);
    }

    private static function runFileSnippet($sampleName, $params = [])
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
