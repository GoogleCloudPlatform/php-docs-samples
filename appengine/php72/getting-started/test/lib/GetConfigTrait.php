<?php
/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf;

use Symfony\Component\Yaml\Yaml;

/**
 * Class GetConfigTrait
 * @package Google\Cloud\Samples\Bookshelf
 *
 * Use this trait to load the project configuration
 */
trait GetConfigTrait
{
    protected static function getConfig()
    {
        // allow the setting of environment variables for testing
        $config = array(
            'google_client_id' => getenv('GOOGLE_CLIENT_ID'),
            'google_client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
            'google_project_id' => getenv('GOOGLE_PROJECT_ID'),
            'bookshelf_backend' => getenv('BOOKSHELF_BACKEND') ?: 'datastore',
            'mongo_url' => getenv('MONGO_URL'),
            'mongo_database' => getenv('MONGO_DATABASE'),
            'mongo_collection' => getenv('MONGO_COLLECTION'),
        );

        $config['mysql_connection_name'] = getenv('MYSQL_CONNECTION_NAME');
        $config['mysql_database_name'] = getenv('MYSQL_DATABASE_NAME');
        $config['mysql_user'] = getenv('MYSQL_USER');
        $config['mysql_password'] = getenv('MYSQL_PASSWORD');
        $config['mysql_port'] = getenv('MYSQL_PORT') ?: 3306;
        $config['postgres_connection_name'] = getenv('POSTGRES_CONNECTION_NAME');
        $config['postgres_database_name'] = getenv('POSTGRES_DATABASE_NAME');
        $config['postgres_user'] = getenv('POSTGRES_USER');
        $config['postgres_password'] = getenv('POSTGRES_PASSWORD');
        $config['postgres_port'] = getenv('POSTGRES_PORT') ?: 5432;

        return $config;
    }
}
