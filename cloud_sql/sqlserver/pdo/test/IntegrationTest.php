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

namespace Google\Cloud\Samples\CloudSQL\SQLServer\Tests;

use Google\Cloud\Samples\CloudSQL\SQLServer\DatabaseTcp;
use Google\Cloud\Samples\CloudSQL\SQLServer\Votes;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\CloudSqlProxyTrait;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class IntegrationTest extends TestCase
{
    use TestTrait;
    use CloudSqlProxyTrait;

    public static function setUpBeforeClass(): void
    {
        $connectionName = self::requireEnv(
            'CLOUDSQL_CONNECTION_NAME_SQLSERVER'
        );
        $socketDir = self::requireEnv('DB_SOCKET_DIR');
        $port = '1433';

        self::startCloudSqlProxy($connectionName, $socketDir, $port);
    }

    public function testTcpConnection()
    {
        $instanceHost = $this->requireEnv('SQLSERVER_HOST');
        $dbPass = $this->requireEnv('SQLSERVER_PASSWORD');
        $dbName = $this->requireEnv('SQLSERVER_DATABASE');
        $dbUser = $this->requireEnv('SQLSERVER_USER');

        putenv("INSTANCE_HOST=$instanceHost");
        putenv("DB_PASS=$dbPass");
        putenv("DB_NAME=$dbName");
        putenv("DB_USER=$dbUser");

        $votes = new Votes(DatabaseTcp::initTcpDatabaseConnection());
        $this->assertIsArray($votes->listVotes());
    }
}
