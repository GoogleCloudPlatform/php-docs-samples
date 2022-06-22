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

namespace Google\Cloud\Samples\CloudSQL\MySQL\Tests;

use Google\Cloud\Samples\CloudSQL\MySQL\DatabaseTcp;
use Google\Cloud\Samples\CloudSQL\MySQL\DatabaseUnix;
use Google\Cloud\Samples\CloudSQL\MySQL\Votes;
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
        $connectionName = self::requireEnv('CLOUDSQL_CONNECTION_NAME_MYSQL');
        $socketDir = self::requireEnv('DB_SOCKET_DIR');
        // '3306' was not working on kokoro we probably just need to update cloud_sql_proxy
        $port = null;

        self::startCloudSqlProxy($connectionName, $socketDir, $port);
    }

    public function testUnixConnection()
    {
        $dbPass = $this->requireEnv('MYSQL_PASSWORD');
        $dbName = $this->requireEnv('MYSQL_DATABASE');
        $dbUser = $this->requireEnv('MYSQL_USER');
        $connectionName = $this->requireEnv('CLOUDSQL_CONNECTION_NAME_MYSQL');
        $socketDir = $this->requireEnv('DB_SOCKET_DIR');
        $instanceUnixSocket = "${socketDir}/${connectionName}";

        putenv("DB_PASS=$dbPass");
        putenv("DB_NAME=$dbName");
        putenv("DB_USER=$dbUser");
        putenv("INSTANCE_UNIX_SOCKET=$instanceUnixSocket");

        $votes = new Votes(DatabaseUnix::initUnixDatabaseConnection());
        $this->assertIsArray($votes->listVotes());

        // Unset environment variables after test run.
        putenv('DB_PASS');
        putenv('DB_NAME');
        putenv('DB_USER');
        putenv('INSTANCE_UNIX_SOCKET');
    }

    public function testTcpConnection()
    {
        $instanceHost = $this->requireEnv('MYSQL_HOST');
        $dbPass = $this->requireEnv('MYSQL_PASSWORD');
        $dbName = $this->requireEnv('MYSQL_DATABASE');
        $dbUser = $this->requireEnv('MYSQL_USER');

        putenv("INSTANCE_HOST=$instanceHost");
        putenv("DB_PASS=$dbPass");
        putenv("DB_NAME=$dbName");
        putenv("DB_USER=$dbUser");

        $votes = new Votes(DatabaseTcp::initTcpDatabaseConnection());
        $this->assertIsArray($votes->listVotes());
    }
}
