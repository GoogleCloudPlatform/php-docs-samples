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

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

// Get container
$container = $app->getContainer();

// Register Twig
$container['view'] = function ($container) {
    return new Slim\Views\Twig(__DIR__ . '/../templates');
};

// Cloud Storage bucket
$container['bucket'] = function ($container) {
    $bucketName = getenv('GOOGLE_STORAGE_BUCKET');
    $storage = new StorageClient([
        'projectId' => $projectId,
    ]);
    return $storage->bucket($bucketName);
};

// Get the Cloud SQL MySQL connection object
$container['cloudsql'] = function ($container) {
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

return $app;
