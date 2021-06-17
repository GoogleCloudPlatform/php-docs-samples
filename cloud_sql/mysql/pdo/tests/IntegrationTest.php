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

use Google\Cloud\Samples\CloudSQL\MySQL\DBInitializer;
use Google\Cloud\Samples\CloudSQL\MySQL\Votes;
use Google\Cloud\TestUtils\TestTrait;
use PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class IntegrationTest extends TestCase
{
    use TestTrait;
    private static $process;

    public static function setUpBeforeClass(): void
    {
        $connectionName = self::requireEnv('CLOUDSQL_CONNECTION_NAME_MYSQL');
        $socketDir = self::requireEnv('DB_SOCKET_DIR');
        self::$process = new Process(['cloud_sql_proxy', '-instances=' . $connectionName . '=tcp:3306,' . $connectionName, '-dir', $socketDir]);
        self::$process->start();
        self::$process->waitUntil(function ($type, $buffer) {
            return str_contains($buffer, 'Ready for new connections');
        });
    }

    public static function tearDownAfterClass(): void
    {
        self::$process->stop();
    }

    public function testUnixConnection()
    {
        $conn_config = [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dbPass = $this->requireEnv('MYSQL_PASSWORD');
        $dbName = $this->requireEnv('MYSQL_DATABASE');
        $dbUser = $this->requireEnv('MYSQL_USER');
        $connectionName = $this->requireEnv('CLOUDSQL_CONNECTION_NAME_MYSQL');
        $socketDir = $this->requireEnv('DB_SOCKET_DIR');

        $votes = new Votes(DBInitializer::initUnixDatabaseConnection(
            $dbUser,
            $dbPass,
            $dbName,
            $connectionName,
            $socketDir,
            $conn_config
        ));
        $this->assertIsArray($votes->listVotes());
    }

    public function testTcpConnection()
    {
        $conn_config = [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dbHost = $this->requireEnv('MYSQL_HOST');
        $dbPass = $this->requireEnv('MYSQL_PASSWORD');
        $dbName = $this->requireEnv('MYSQL_DATABASE');
        $dbUser = $this->requireEnv('MYSQL_USER');

        $votes = new Votes(DBInitializer::initTcpDatabaseConnection(
            $dbUser,
            $dbPass,
            $dbName,
            $dbHost,
            $conn_config
        ));
        $this->assertIsArray($votes->listVotes());
    }
}
