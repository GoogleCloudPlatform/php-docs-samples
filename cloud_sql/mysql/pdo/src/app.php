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

use Google\Cloud\Samples\CloudSQL\MySQL\Votes;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$container = new Container;
AppFactory::setContainer(new Psr11Container($container));
$app = AppFactory::create();

$container['votes'] = function (Container $container) {
    return new Votes($container['db']);
};

$container['db'] = function () {
    $username = getenv("DB_USER");
    $password = getenv("DB_PASS");
    $schema = getenv("DB_NAME");
    $hostname = getenv("DB_HOSTNAME") ?: "127.0.0.1";
    $cloud_sql_connection_name = getenv("CLOUD_SQL_CONNECTION_NAME");

    try {
        // # [START cloud_sql_mysql_pdo_create]
        // // $username = 'your_db_user';
        // // $password = 'yoursupersecretpassword';
        // // $schema = 'your_db_name';
        // // $cloud_sql_connection_name = getenv("CLOUD_SQL_CONNECTION_NAME");

        if ($cloud_sql_connection_name) {
            // Connect using UNIX sockets
            $dsn = sprintf(
                'mysql:dbname=%s;unix_socket=/Users/jdp/cloudsql/%s',
                $schema,
                $cloud_sql_connection_name
            );
        } else {
            // Connect using TCP
            // $hostname = '127.0.0.1';
            $dsn = sprintf('mysql:dbname=%s;host=%s', $schema, $hostname);
        }

        $conn = new PDO($dsn, $username, $password);
        # [END cloud_sql_mysql_pdo_create]
    } catch (PDOException $e) {
        throw new RuntimeException(
            "Could not connect to the Cloud SQL Database. " .
            "Refer to https://cloud.google.com/sql/docs/mysql/connect-admin-proxy " .
            "for more assistance. The PDO error was " . $e->getMessage(),
            $e->getCode(),
            $e
        );
    }

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
};

$container['view'] = function() {
    return Twig::create(__DIR__ . '/../views');
};

$app->add(TwigMiddleware::createFromContainer($app));

$app->addErrorMiddleware(true, false, false);

return $app;
