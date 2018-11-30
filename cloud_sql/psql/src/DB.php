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
    private $connection;

    public function __construct()
    {
        $config = [
            "username" => getenv("DB_USER"),
            "password" => getenv("DB_PASS"),
            "schema" => getenv("DB_NAME"),
            "hostname" => getenv("DB_HOSTNAME") ?: "127.0.0.1",
            "cloud_sql_instance_name" => getenv("CLOUD_SQL_INSTANCE_NAME")
        ];

        $this->connection = $this->connect($config);
    }

    private function connect($config)
    {
        $dsn = "pgsql:dbname={$config['schema']};host={$config['hostname']}";

        if ($config["cloud_sql_instance_name"] != "") {
            $dsn = "pgsql:dbname={$config['schema']};host=/cloudsql/{$config['cloud_sql_instance_name']}";
        }

        return new PDO($dsn, $config['username'], $config['password']);
    }

    public function get_connection()
    {
        return $this->connection;
    }
}
