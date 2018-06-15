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

# [START gae_cloudsql_example]
// Connect to CloudSQL from App Engine.
$dsn = getenv('CLOUDSQL_DSN');
$user = getenv('CLOUDSQL_USER');
$password = getenv('CLOUDSQL_PASSWORD');
if (!isset($dsn, $user) || false === $password) {
    throw new Exception('Set CLOUDSQL_DSN, CLOUDSQL_USER, and CLOUDSQL_PASSWORD environment variables');
}

// Create the PDO object to talk to CloudSQL
$db = new PDO($dsn, $user, $password);

// create the tables if they don't exist
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS entries ('
    . 'guestName VARCHAR(255), '
    . 'content VARCHAR(255))');
$result = $stmt->execute();

if (false === $result) {
    exit("Error: " . $stmt->errorInfo()[2]);
}

// Insert a new row into the guestbook on POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare('INSERT INTO entries (guestName, content) VALUES (:name, :content)');
    $result = $stmt->execute([
        ':name' => $_POST['name'],
        ':content' => $_POST['content'],
    ]);
    if (false === $result) {
        print("Error: " . $stmt->errorInfo()[2]);
    }
}

// Show existing guestbook entries.
$results = $db->query('SELECT * from entries');

?>
<?php ?>
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
<?php # [END gae_cloudsql_example]?>
