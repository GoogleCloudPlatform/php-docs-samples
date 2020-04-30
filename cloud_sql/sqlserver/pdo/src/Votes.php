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

namespace Google\Cloud\Samples\CloudSQL\SQLServer;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Manage votes using the Cloud SQL database.
 */
class Votes
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @param PDO $connection A connection to the database.
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Creates the table if it does not yet exist.
     *
     * @return void
     */
    public function createTableIfNotExists()
    {
        $existsStmt = "SELECT * FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_NAME = ?";

        $stmt = $this->connection->prepare($existsStmt);
        $stmt->execute(['votes']);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the table does not exist, create it.
        if (!$row) {
            $sql = "CREATE TABLE votes (
                vote_id INT NOT NULL IDENTITY,
                time_cast DATETIME NOT NULL,
                vote_value VARCHAR(6) NOT NULL,
                PRIMARY KEY (vote_id)
            );";

            $this->connection->exec($sql);
        }
    }

    /**
     * Returns a list of the last five votes
     *
     * @return array
     */
    public function listVotes() : array
    {
        $sql = "SELECT TOP 5 vote_value, time_cast FROM votes ORDER BY time_cast DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the number of votes cast for a given value.
     *
     * @param string $value
     * @param int
     */
    public function getCountByValue(string $value) : int
    {
        $sql = "SELECT COUNT(vote_id) as voteCount FROM votes WHERE vote_value = ?";

        $statement = $this->connection->prepare($sql);
        $statement->execute([$value]);

        return (int) $statement->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Insert a new vote into the database
     *
     * @param string $value The value to vote for.
     * @return boolean
     */
    public function insertVote(string $value) : bool
    {
        $conn = $this->connection;
        $res = false;

        # [START cloud_sql_sqlserver_pdo_connection]
        // Use prepared statements to guard against SQL injection.
        $sql = "INSERT INTO votes (time_cast, vote_value) VALUES (GETDATE(), :voteValue)";

        try {
            $statement = $conn->prepare($sql);
            $statement->bindParam('voteValue', $value);

            $res = $statement->execute();
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Could not insert vote into database. The PDO exception was " .
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        # [END cloud_sql_sqlserver_pdo_connection]

        return $res;
    }
}
