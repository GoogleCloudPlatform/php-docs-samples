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

/**
 * Create a new Silex Application with Twig.  Configure it for debugging.
 * Follows Silex Skeleton pattern.
 */
use Google\Cloud\Samples\Bookshelf\DataModel\CloudSql;
use Google\Cloud\Storage\StorageClient;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

$app = new Application();

// register twig
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../templates',
    'twig.options' => array(
        'strict_variables' => false,
    ),
));

// Cloud Storage
$app['cloud_storage_bucket'] = function ($app) {
    $bucketName = getenv('GOOGLE_BUCKET_NAME');

    $storage = new StorageClient([
        'projectId' => $projectId,
    ]);
    return $storage->bucket($bucketName);
};

// Get the Cloud SQL MySQL connection object
$app['cloud_sql'] = function ($app) {
    // Data Model
    $dbName = getenv('CLOUDSQL_DATABASE_NAME') ?: 'bookshelf';
    $connection = getenv('CLOUDSQL_CONNECTION_NAME');
    $dsn = "mysql:unix_socket=/cloudsql/${connection};dbname=${dbName}";
    $user = getenv('CLOUDSQL_USER');
    $password = getenv('CLOUDSQL_PASSWORD');
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return new CloudSql($pdo);
};

// Turn on debug locally
if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'])
    || php_sapi_name() === 'cli-server'
) {
    $app['debug'] = true;
} else {
    $app['debug'] = filter_var(
        getenv('BOOKSHELF_DEBUG'),
        FILTER_VALIDATE_BOOLEAN
    );
}

// add service parameters
$app['bookshelf.page_size'] = 10;

return $app;
