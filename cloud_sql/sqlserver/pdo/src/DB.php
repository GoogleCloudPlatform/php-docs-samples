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

namespace Google\Cloud\Samples\CloudSQL\SQLServer;

use PDO;

class DB
{
    public static function createPdoConnection()
    {
        $username = getenv("DB_USER");
        $password = getenv("DB_PASS");
        $schema = getenv("DB_NAME");
        $hostname = getenv("DB_HOSTNAME") ?: "(local)";

        # [START cloud_sql_sqlserver_pdo_create]
        // $username = 'your_db_user';
        // $password = 'yoursupersecretpassword';
        // $schema = 'your_db_name';
        // $hostname = 'your_hostname';

        $dsn = sprintf('sqlsrv:server=%s;Database=%s', $hostname, $schema);

        return new PDO($dsn, $username, $password);
        # [END cloud_sql_sqlserver_pdo_create]
    }
}
