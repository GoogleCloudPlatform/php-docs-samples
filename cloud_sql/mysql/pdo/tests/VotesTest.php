<?php
/**
 * Copyright 2020 Google LLC
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

namespace Google\Cloud\Samples\CloudSQL\MySQL\Tests;

use Google\Cloud\Samples\CloudSQL\MySQL\Votes;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;

class VotesTest extends TestCase
{
    private $conn;

    public function setUp() : void
    {
        $this->conn = $this->prophesize(PDO::class);
    }

    public function testCreateTableIfNotExistsTableExists()
    {
        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->execute()->shouldBeCalled();

        $this->conn->prepare('SELECT 1 FROM votes')
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $this->conn->exec(Argument::any())->shouldNotBeCalled();

        $votes = new Votes($this->conn->reveal());
        $votes->createTableIfNotExists();
    }

    public function testCreateTableIfNotExistsTableDoesNotExist()
    {
        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->execute()->shouldBeCalled()->willThrow(
            new PDOException('foo')
        );

        $this->conn->prepare('SELECT 1 FROM votes')
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $this->conn->exec(Argument::containingString('CREATE TABLE votes'))
            ->shouldBeCalled();

        $votes = new Votes($this->conn->reveal());
        $votes->createTableIfNotExists();
    }

    public function testListVotes()
    {
        $rows = [
            ['foo' => 'bar']
        ];

        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->execute()->shouldBeCalled();
        $stmt->fetchAll(PDO::FETCH_ASSOC)->shouldBeCalled()
            ->willReturn($rows);

        $this->conn->prepare(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $votes = new Votes($this->conn->reveal());

        $this->assertEquals($rows, $votes->listVotes());
    }

    public function testGetCountByValue()
    {
        $val = 'TABS';
        $res = 10;

        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->execute([$val])
            ->shouldBeCalled();

        $stmt->fetch(PDO::FETCH_COLUMN)
            ->shouldBeCalled()
            ->willReturn((string) $res);

        $this->conn->prepare(Argument::containingString('SELECT COUNT(vote_id)'))
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $votes = new Votes($this->conn->reveal());

        $this->assertEquals($res, $votes->getCountByValue($val));
    }

    public function testInsertVote()
    {
        $val = 'TABS';

        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->bindParam('voteValue', $val)
            ->shouldBeCalled();

        $stmt->execute()->shouldBeCalled()->willReturn(true);

        $this->conn->prepare(Argument::containingString('INSERT INTO votes'))
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $votes = new Votes($this->conn->reveal());
        $this->assertTrue($votes->insertVote($val));
    }

    public function testInsertVoteFailed()
    {
        $this->expectException(RuntimeException::class);

        $val = 'TABS';

        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->bindParam('voteValue', $val)
            ->shouldBeCalled();

        $stmt->execute()->shouldBeCalled()
            ->willThrow(new PDOException('Op failed'));

        $this->conn->prepare(Argument::containingString('INSERT INTO votes'))
            ->shouldBeCalled()
            ->willReturn($stmt->reveal());

        $votes = new Votes($this->conn->reveal());
        $votes->insertVote($val);
    }
}
