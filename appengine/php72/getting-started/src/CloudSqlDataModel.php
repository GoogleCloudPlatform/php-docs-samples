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

namespace Google\Cloud\Samples\AppEngine\GettingStarted;

use PDO;

/**
 * Class CloudSql is a wrapper for making calls to a Cloud SQL MySQL database.
 */
class CloudSqlDataModel
{
    private $dsn;
    private $user;
    private $password;

    /**
     * Creates the SQL books table if it doesn't already exist.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        $columns = array(
            'id serial PRIMARY KEY ',
            'title VARCHAR(255)',
            'author VARCHAR(255)',
            'published_date VARCHAR(255)',
            'image_url VARCHAR(255)',
            'description VARCHAR(255)',
            'created_by VARCHAR(255)',
            'created_by_id VARCHAR(255)',
        );

        $this->columnNames = array_map(function ($columnDefinition) {
            return explode(' ', $columnDefinition)[0];
        }, $columns);
        $columnText = implode(', ', $columns);

        $this->pdo->query("CREATE TABLE IF NOT EXISTS books ($columnText)");
    }

    /**
     * Throws an exception if $book contains an invalid key.
     *
     * @param $book array
     *
     * @throws \Exception
     */
    private function verifyBook($book)
    {
        if ($invalid = array_diff_key($book, array_flip($this->columnNames))) {
            throw new \Exception(sprintf(
                'unsupported book properties: "%s"',
                implode(', ', $invalid)
            ));
        }
    }

    public function listBooks($limit = 10, $cursor = 0)
    {
        $pdo = $this->pdo;
        $query = 'SELECT * FROM books WHERE id > :cursor ORDER BY id LIMIT :limit';
        $statement = $pdo->prepare($query);
        $statement->bindValue(':cursor', $cursor, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        // Uncomment this while loop to output the results
        // while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        //     var_dump($row);
        // }
        $rows = array();
        $nextCursor = null;
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            array_push($rows, $row);
            if (count($rows) == $limit) {
                $nextCursor = $row['id'];
                break;
            }
        }

        return ['books' => $rows, 'cursor' => $nextCursor];
    }

    public function create($book, $id = null)
    {
        $this->verifyBook($book);
        if ($id) {
            $book['id'] = $id;
        }
        $names = array_keys($book);
        $placeHolders = array_map(function ($key) {
            return ":$key";
        }, $names);
        $pdo = $this->pdo;
        $sql = sprintf(
            'INSERT INTO books (%s) VALUES (%s)',
            implode(', ', $names),
            implode(', ', $placeHolders)
        );
        $statement = $pdo->prepare($sql);
        $statement->execute($book);
        return $this->pdo->lastInsertId();
    }

    public function read($id)
    {
        $pdo = $this->pdo;
        // [START gae_php_app_cloudsql_query]
        $statement = $pdo->prepare('SELECT * FROM books WHERE id = :id');
        $statement->bindValue('id', $id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        // [END gae_php_app_cloudsql_query]
        return $result;
    }

    public function update($book)
    {
        $this->verifyBook($book);
        $assignments = array_map(
            function ($column) {
                return "$column=:$column";
            },
            $this->columnNames
        );
        $assignmentString = implode(',', $assignments);
        $sql = "UPDATE books SET $assignmentString WHERE id = :id";
        $statement = $this->pdo->prepare($sql);
        $values = array_merge(
            array_fill_keys($this->columnNames, null),
            $book
        );
        return $statement->execute($values);
    }

    public function delete($id)
    {
        $statement = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        $statement->bindValue('id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount();
    }
}
