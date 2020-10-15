<?php
/**
 * Copyright 2016 Google Inc.
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

# [START gae_php_mysql_app]
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
    /** @var PDO $db */
    $db = $app['database'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    // Show existing guestbook entries.
    $results = $db->query('SELECT * from entries');

    return $twig->render('cloudsql.html.twig', [
        'results' => $results,
    ]);
});

$app->post('/', function (Request $request) use ($app) {
    /** @var PDO $db */
    $db = $app['database'];

    $name = $request->request->get('name');
    $content = $request->request->get('content');

    if ($name && $content) {
        $stmt = $db->prepare('INSERT INTO entries (guestName, content) VALUES (:name, :content)');
        $stmt->execute([
            ':name' => $name,
            ':content' => $content,
        ]);
    }

    return $app->redirect('/');
});

// function to return the PDO instance
$app['database'] = function () use ($app) {
    // Connect to CloudSQL from App Engine.
    $dsn = getenv('MYSQL_DSN');
    $user = getenv('MYSQL_USER');
    $password = getenv('MYSQL_PASSWORD');
    if (!isset($dsn, $user) || false === $password) {
        throw new Exception('Set MYSQL_DSN, MYSQL_USER, and MYSQL_PASSWORD environment variables');
    }

    $db = new PDO($dsn, $user, $password);

    return $db;
};
# [END gae_php_mysql_app]

$app->get('create_tables', function () use ($app) {
    /** @var PDO $db */
    $db = $app['database'];
    // create the tables
    $stmt = $db->prepare('CREATE TABLE IF NOT EXISTS entries ('
        . 'entryID INT NOT NULL AUTO_INCREMENT, '
        . 'guestName VARCHAR(255), '
        . 'content VARCHAR(255), '
        . 'PRIMARY KEY(entryID))');
    $result = $stmt->execute();

    if (false === $result) {
        return sprintf("Error: %s\n", $stmt->errorInfo()[2]);
    } else {
        return 'Tables created';
    }
});

return $app;
