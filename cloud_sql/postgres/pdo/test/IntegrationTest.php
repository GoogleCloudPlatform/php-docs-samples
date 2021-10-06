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

namespace Google\Cloud\Samples\CloudSQL\Postgres\Tests;

use Google\Cloud\Samples\CloudSQL\Postgres\DBInitializer;
use Google\Cloud\Samples\CloudSQL\Postgres\Votes;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\CloudSqlProxyTrait;
use PDO;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    use TestTrait;
    use CloudSqlProxyTrait;

    public static function setUpBeforeClass(): void
    {
        $connectionName = self::requireEnv('CLOUDSQL_CONNECTION_NAME_POSTGRES');
        $socketDir = self::requireEnv('DB_SOCKET_DIR');
        $port = '5432';

        self::startCloudSqlProxy($connectionName, $socketDir, $port);
    }

    public function testUnixConnection()
    {
        $connConfig = [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dbPass = $this->requireEnv('POSTGRES_PASSWORD');
        $dbName = $this->requireEnv('POSTGRES_DATABASE');
        $dbUser = $this->requireEnv('POSTGRES_USER');
        $connectionName = $this->requireEnv('CLOUDSQL_CONNECTION_NAME_POSTGRES');
        $socketDir = $this->requireEnv('DB_SOCKET_DIR');

        $votes = new Votes(DBInitializer::initUnixDatabaseConnection(
            $dbUser,
            $dbPass,
            $dbName,
            $connectionName,
            $socketDir,
            $connConfig
        ));
        $this->assertIsArray($votes->listVotes());
    }

    public function testTcpConnection()
    {
        $connConfig = [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dbHost = $this->requireEnv('POSTGRES_HOST');
        $dbPass = $this->requireEnv('POSTGRES_PASSWORD');
        $dbName = $this->requireEnv('POSTGRES_DATABASE');
        $dbUser = $this->requireEnv('POSTGRES_USER');

        $votes = new Votes(DBInitializer::initTcpDatabaseConnection(
            $dbUser,
            $dbPass,
            $dbName,
            $dbHost,
            $connConfig
        ));
        $this->assertIsArray($votes->listVotes());
    }
}
