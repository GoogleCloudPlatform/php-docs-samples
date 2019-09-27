<?php
# Copyright 2018 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

namespace Google\Cloud\Samples\CloudSQL\Postgres;

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
        $sql = "
        CREATE TABLE IF NOT EXISTS votes (
            vote_id SERIAL NOT NULL,
            time_cast TIMESTAMP NOT NULL,
            candidate VARCHAR(6) NOT NULL,
            PRIMARY KEY (vote_id)
        );";
        $this->connection->exec($sql);
    }

    public function list()
    {
        $sql = "SELECT candidate, time_cast FROM votes ORDER BY time_cast DESC LIMIT 5";
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function count_candidates()
    {
        $sql = "SELECT COUNT(vote_id) FROM votes WHERE candidate = ?";
        $count = [];

        $statement = $this->connection->prepare($sql);

        //tabs
        $statement->execute(['TABS']);
        $count['tabs'] = $statement->fetch()['count'];

        //spaces
        $statement->execute(['SPACES']);
        $count['spaces'] = $statement->fetch()['count'];

        return $count;
    }

    public function save($team)
    {
        $sql = "INSERT INTO votes (time_cast, candidate) VALUES ('NOW()', :candidate)";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam('candidate', $team);

        if ($statement->execute()) {
            return "Vote successfully cast for '$team'";
        }

        return $this->connection->errorInfo();
    }
}
