<?php
/*
 * Copyright 2020 Google LLC.
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

declare(strict_types=1);

# [START cloud_sql_postgres_pdo_connect_unix]
namespace Google\Cloud\Samples\CloudSQL\Postgres;

use PDO;
use PDOException;
use RuntimeException;
use TypeError;

class DatabaseUnix
{

    /**
     *  @param $username string username of the database user
     *  @param $password string password of the database user
     *  @param $dbName string name of the target database
     *  @param $instanceUnixSocket string '/cloudsql/' and the cloudsql instance connection name
     *  @param $connConfig array driver-specific options for PDO
     */
    public static function initUnixDatabaseConnection(
        string $username,
        string $password,
        string $dbName,
        string $instanceUnixSocket,
        array $connConfig
    ): PDO {
        try {

            // $username = 'your_db_user';
            // $password = 'yoursupersecretpassword';
            // $dbName = 'your_db_name';
            // $instanceUnixSocket = '/cloudsql/project:region:instance';

            // Connect using UNIX sockets
            $dsn = sprintf(
                'pgsql:dbname=%s;host=%s',
                $dbName,
                $instanceUnixSocket
            );

            // Connect to the database.
            $conn = new PDO($dsn, $username, $password, $connConfig);
        } catch (TypeError $e) {
            throw new RuntimeException(
                sprintf(
                    'Invalid or missing configuration! Make sure you have set ' .
                    '$username, $password, $dbName, ' .
                    'and $instanceUnixSocket (for UNIX socket mode). ' .
                    'The PHP error was %s',
                    $e->getMessage()
                ),
                (int) $e->getCode(),
                $e
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                sprintf(
                    'Could not connect to the Cloud SQL Database. Check that ' .
                    'your username and password are correct, that the Cloud SQL ' .
                    'proxy is running, and that the database exists and is ready ' .
                    'for use. For more assistance, refer to %s. The PDO error was %s',
                    'https://cloud.google.com/sql/docs/postgres/connect-external-app',
                    $e->getMessage()
                ),
                (int) $e->getCode(),
                $e
            );
        }

        return $conn;
    }
}
# [END cloud_sql_postgres_pdo_connect_unix]
