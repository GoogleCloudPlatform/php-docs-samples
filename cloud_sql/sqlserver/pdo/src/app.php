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

use Google\Cloud\Samples\CloudSQL\SQLServer\Votes;
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
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $dbName = getenv('DB_NAME');
    $hostname = getenv('DB_HOSTNAME') ?: '127.0.0.1';

    try {
        // # [START cloud_sql_sqlserver_pdo_create]
        // // $username = 'your_db_user';
        // // $password = 'yoursupersecretpassword';
        // // $dbName = 'your_db_name';
        // // $hostname = "127.0.0.1";

        $dsn = sprintf('sqlsrv:server=%s;Database=%s', $hostname, $dbName);

        // Connect to the database.
        # [START cloud_sql_sqlserver_pdo_timeout]
        // Here we set the connection timeout to five seconds and ask PDO to
        // throw an exception if any errors occur.
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        # [END cloud_sql_sqlserver_pdo_timeout]
        # [END cloud_sql_sqlserver_pdo_create]
    } catch (TypeError $e) {
        throw new RuntimeException(
            sprintf(
                'Invalid or missing configuration! Make sure you have set ' .
                '$username, $password, $dbName, and $hostname. ' .
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
            (int) $e->getCode(),
            $e
        );
    }

    return $conn;
};

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
