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

// Create and set the dependency injection container.
$container = new Container;
AppFactory::setContainer(new Psr11Container($container));

// add the votes manager to the container.
$container['votes'] = function (Container $container) {
    return new Votes($container['db']);
};

// Setup the database connection in the container.
$container['db'] = function () {
    # [START cloud_sql_mysql_pdo_timeout]
    // Here we set the connection timeout to five seconds and ask PDO to
    // throw an exception if any errors occur.
    $conn_config = [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    # [END cloud_sql_mysql_pdo_timeout]

    if (empty(getenv('DB_HOST'))) {
        return init_unix_database_connection($conn_config);
    } else {
        return init_tcp_database_connection($conn_config);
    }
};

/**
 *  @param $conn_config array driver-specific options for PDO
 */
function init_tcp_database_connection(array $conn_config) : PDO
{
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $db_name = getenv('DB_NAME');
    $host = getenv('DB_HOST');

    try {
        # [START cloud_sql_mysql_pdo_create_tcp]
        // // $username = 'your_db_user';
        // // $password = 'yoursupersecretpassword';
        // // $db_name = 'your_db_name';
        // // $host = "127.0.0.1";

        // Connect using TCP
        $dsn = sprintf('mysql:dbname=%s;host=%s', $db_name, $host);

        // Connect to the database.
        $conn = new PDO($dsn, $username, $password, $conn_config);
        # [END cloud_sql_mysql_pdo_create_tcp]
    } catch (TypeError $e) {
        throw new RuntimeException(
            sprintf(
                'Invalid or missing configuration! Make sure you have set ' .
                '$username, $password, $db_name, and $host (for TCP mode) ' .
                'or $cloud_sql_connection_name (for UNIX socket mode). ' .
                'The PHP error was %s',
                $e->getMessage()
            ),
            $e->getCode(),
            $e
        );
    } catch (PDOException $e) {
        throw new RuntimeException(
            sprintf(
                'TCP Could not connect to the Cloud SQL Database. Check that ' .
                'your username and password are correct, that the Cloud SQL ' .
                'proxy is running, and that the database exists and is ready ' .
                'for use. For more assistance, refer to %s. The PDO error was %s',
                'https://cloud.google.com/sql/docs/mysql/connect-external-app',
                $e->getMessage()
            ),
            $e->getCode(),
            $e
        );
    }

    return $conn;
}

/**
 *  @param $conn_config array driver-specific options for PDO
 */
function init_unix_database_connection(array $conn_config) : PDO
{
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $db_name = getenv('DB_NAME');
    $cloud_sql_connection_name = getenv('CLOUD_SQL_CONNECTION_NAME');
    $socket_dir = getenv('DB_SOCKET_DIR') ?: '/cloudsql';

    try {
        // # [START cloud_sql_mysql_pdo_create_socket]
        // // $username = 'your_db_user';
        // // $password = 'yoursupersecretpassword';
        // // $db_name = 'your_db_name';
        // // $cloud_sql_connection_name = getenv("CLOUD_SQL_CONNECTION_NAME");
        // // $socket_dir = getenv('DB_SOCKET_DIR') ?: '/cloudsql';

        // Connect using UNIX sockets
        $dsn = sprintf(
            'mysql:dbname=%s;unix_socket=%s/%s',
            $db_name,
            $socket_dir,
            $cloud_sql_connection_name
        );

        // Connect to the database.
        $conn = new PDO($dsn, $username, $password, $conn_config);
        # [END cloud_sql_mysql_pdo_create_socket]
    } catch (TypeError $e) {
        throw new RuntimeException(
            sprintf(
                'Invalid or missing configuration! Make sure you have set ' .
                '$username, $password, $db_name, and $host (for TCP mode) ' .
                'or $cloud_sql_connection_name (for UNIX socket mode). ' .
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
                'https://cloud.google.com/sql/docs/mysql/connect-external-app',
                $e->getMessage()
            ),
            $e->getCode(),
            $e
        );
    }

    return $conn;
}

// Configure the templating engine.
$container['view'] = function () {
    return Twig::create(__DIR__ . '/../views');
};

// Create the application.
$app = AppFactory::create();

// Add the twig middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Setup error handlinmg
$app->addErrorMiddleware(true, false, false);

return $app;
