<?php
# Copyright 2018 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

namespace Google\Cloud\Samples\CloudSQL\Postgres;

use PDO;

class DB
{
    public static function createPdoConnection()
    {
        $username = getenv("DB_USER");
        $password = getenv("DB_PASS");
        $schema = getenv("DB_NAME");
        $hostname = getenv("DB_HOSTNAME") ?: "127.0.0.1";
        $cloud_sql_connection_name = getenv("CLOUD_SQL_CONNECTION_NAME");
        # [START cloud_sql_postgres_pdo_create]
        // $username = 'your_db_user';
        // $password = 'yoursupersecretpassword';
        // $schema = 'your_db_name';
        // $cloud_sql_connection_name = 'Your Cloud SQL Connection name';

        if ($cloud_sql_connection_name) {
            // Connect using UNIX sockets
            $dsn = sprintf(
                'pgsql:dbname=%s;host=/cloudsql/%s',
                $schema,
                $cloud_sql_connection_name
            );
        } else {
            // Connect using TCP
            // $hostname = '127.0.0.1';
            $dsn = sprintf('pgsql:dbname=%s;host=%s', $schema, $hostname);
        }

        return new PDO($dsn, $username, $password);
        # [END cloud_sql_postgres_pdo_create]
    }
}
