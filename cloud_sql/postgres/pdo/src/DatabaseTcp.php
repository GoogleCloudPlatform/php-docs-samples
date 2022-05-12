<?php
/*
 * Copyright 2022 Google LLC.
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

# [START cloud_sql_postgres_pdo_connect_tcp]
namespace Google\Cloud\Samples\CloudSQL\Postgres;

use PDO;
use PDOException;
use RuntimeException;
use TypeError;

class DatabaseTcp
{

    /**
     *  @param $instanceHost string IP address or domain of the target cloudsql instance
     *  @param $connConfig array driver-specific options for PDO
     */
    public static function initTcpDatabaseConnection(
        string $instanceHost,
        array $connConfig
    ): PDO {
        try {
            // $username = 'your_db_user';
            // $password = 'yoursupersecretpassword';
            // $dbName = 'your_db_name';
            // $instanceHost = '127.0.0.1';

            // Note: Saving credentials in environment variables is convenient, but not
            // secure - consider a more secure solution such as
            // Cloud Secret Manager (https://cloud.google.com/secret-manager) to help
            // keep secrets safe.
            $username = getenv('DB_USER');
            $password = getenv('DB_PASS');
            $dbName = getenv('DB_NAME');

            if (empty($username = getenv('DB_USER'))) {
                throw new RuntimeException('Must supply $DB_USER environment variables');
            }
            if (empty($password = getenv('DB_PASS'))) {
                throw new RuntimeException('Must supply $DB_PASS environment variables');
            }
            if (empty($dbName = getenv('DB_NAME'))) {
                throw new RuntimeException('Must supply $DB_NAME environment variables');
            }

            // Connect using TCP
            $dsn = sprintf('pgsql:dbname=%s;host=%s', $dbName, $instanceHost);

            // Connect to the database
            $conn = new PDO($dsn, $username, $password, $connConfig);
        } catch (TypeError $e) {
            throw new RuntimeException(
                sprintf(
                    'Invalid or missing configuration! Make sure you have set ' .
                    '$username, $password, $dbName, and $instanceHost (for TCP mode). ' .
                    'The PHP error was %s',
                    $e->getMessage()
                ),
                $e->getCode(),
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
                $e->getCode(),
                $e
            );
        }

        return $conn;
    }
}
# [END cloud_sql_postgres_pdo_connect_tcp]
