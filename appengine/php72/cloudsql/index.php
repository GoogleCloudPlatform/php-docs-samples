<?php
/**
 * Copyright 2018 Google Inc.
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

// Ensure the required environment variables are set to run the application
if (!getenv('CLOUDSQL_DSN') || !getenv('CLOUDSQL_USER') || false === getenv('CLOUDSQL_PASSWORD')) {
    die('Set CLOUDSQL_DSN, CLOUDSQL_USER, and CLOUDSQL_PASSWORD environment variables');
}

# [START gae_cloudsql_example]
// If the unix socket is unavailable, try to connect using TCP. This will work
// if you're running a local MySQL server or using the Cloud SQL proxy, for example:
//
//     $ cloud_sql_proxy -instances=your-connection-name=tcp:3306
//
// This will mean your DSN for connecting locally to Cloud SQL would look like this:
//
//     // for MySQL
//     $dsn = "mysql:dbname=DATABASE;host=127.0.0.1";
//     // for PostgreSQL
//     $dsn = "pgsql:dbname=DATABASE;host=127.0.0.1";
//
$dsn = getenv('CLOUDSQL_DSN');
$user = getenv('CLOUDSQL_USER');
$password = getenv('CLOUDSQL_PASSWORD');

// create the PDO client
$db = new PDO($dsn, $user, $password);

// create the tables if they don't exist
$sql = 'CREATE TABLE IF NOT EXISTS entries (guestName VARCHAR(255), content VARCHAR(255))';
$stmt = $db->prepare($sql);
$stmt->execute();

// Insert a new row into the guestbook on POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare('INSERT INTO entries (guestName, content) VALUES (:name, :content)');
    $stmt->execute([
        ':name' => $_POST['name'],
        ':content' => $_POST['content'],
    ]);
}

// Query existing guestbook entries.
$results = $db->query('SELECT * from entries');

// Now you can use the PDOStatement object to print or iterate over the results:
//
//     var_dump($results->fetchAll(PDO::FETCH_ASSOC));
# [END gae_cloudsql_example]
?>

<html>
    <body>
        <?php if ($results->rowCount() > 0): ?>
            <h2>Guestbook Entries</h2>
            <?php foreach ($results as $row): ?>
                <div><strong> <?= $row['guestName'] ?></strong>: <?= $row['content'] ?></div>
            <?php endforeach ?>
        <?php endif ?>

        <h2>Sign the Guestbook</h2>
        <form action="/" method="post">
            <div>Name: <input name="name" /></div>
            <div><textarea name="content" rows="3" cols="60"></textarea></div>
            <div><input type="submit" value="Sign Guestbook"></div>
        </form>
    </body>
</html>
