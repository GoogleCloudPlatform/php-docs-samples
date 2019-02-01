<?php

/*
 * Copyright 2018 Google LLC All Rights Reserved.
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
use Google\Cloud\Samples\AppEngine\GettingStarted\CloudSqlDataModel;
// [START gae_php_app_storage_client_import]
use Google\Cloud\Storage\StorageClient;

// [END gae_php_app_storage_client_import]

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
    // [START gae_php_app_storage_client_setup]
    // Your Google Cloud Storage bucket name and Project ID can be configured
    // however fits your application best.
    // $projectId = 'YOUR_PROJECT_ID';
    // $bucketName = 'YOUR_BUCKET_NAME';
    $storage = new StorageClient([
        'projectId' => $projectId,
    ]);
    $bucket = $storage->bucket($bucketName);
    // [END gae_php_app_storage_client_setup]
    return $bucket;
};

// Get the Cloud SQL MySQL connection object
$container['cloudsql'] = function ($container) {
    // Data Model
    $dbName = getenv('CLOUDSQL_DATABASE_NAME') ?: 'bookshelf';
    $dbConn = getenv('CLOUDSQL_CONNECTION_NAME');
    $dbUser = getenv('CLOUDSQL_USER');
    $dbPass = getenv('CLOUDSQL_PASSWORD');
    // [START gae_php_app_cloudsql_client_setup]
    // Fill the variables below to match your Cloud SQL configuration.
    // $dbConn = 'YOUR_CLOUDSQL_CONNECTION_NAME';
    // $dbName = 'YOUR_CLOUDSQL_DATABASE_NAME';
    // $dbUser = 'YOUR_CLOUDSQL_USER';
    // $dbPass = 'YOUR_CLOUDSQL_PASSWORD';
    $dsn = "mysql:unix_socket=/cloudsql/${dbConn};dbname=${dbName}";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    // [END gae_php_app_cloudsql_client_setup]
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return new CloudSqlDataModel($pdo);
};

return $app;
