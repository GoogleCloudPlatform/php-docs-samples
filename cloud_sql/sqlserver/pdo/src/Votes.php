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

namespace Google\Cloud\Samples\CloudSQL\SQLServer;

use PDO;

class Votes
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->create_table();
    }

    private function create_table()
    {
        $tableName = "votes";

        $existsStmt = "SELECT * FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_NAME = ?";

        $stmt = $this->connection->prepare($existsStmt);
        $stmt->execute([$tableName]);

        // If table does not exist, create it!
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $sql = "
            CREATE TABLE votes (
                vote_id INT NOT NULL IDENTITY,
                time_cast DATETIME NOT NULL,
                candidate VARCHAR(6) NOT NULL,
                PRIMARY KEY (vote_id)
            );";
            if ($this->connection->exec($sql) !== 1) {
                print_r($this->connection->errorInfo());
                exit;
            }
        }
    }

    public function list()
    {
        $sql = "SELECT TOP 5 candidate, time_cast FROM votes ORDER BY time_cast DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function count_candidates()
    {
        $sql = "SELECT COUNT(vote_id) as voteCount FROM votes WHERE candidate = ?";
        $count = [];

        $statement = $this->connection->prepare($sql);

        //tabs
        $statement->execute(['TABS']);
        $count['tabs'] = $statement->fetch()[0];

        //spaces
        $statement->execute(['SPACES']);
        $count['spaces'] = $statement->fetch()[0];

        return $count;
    }

    public function save($team)
    {
        $sql = "INSERT INTO votes (time_cast, candidate) VALUES (GETDATE(), :candidate)";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam('candidate', $team);

        if ($statement->execute()) {
            return "Vote successfully cast for '$team'";
        }

        return print_r($statement->errorInfo(), true);
    }
}
