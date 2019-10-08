<?php
/**
 * Copyright 2019 Google Inc.
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

namespace Google\Cloud\Firestore;

use Google\Cloud\Firestore\SolutionCounters;
use Google\Cloud\Firestore\Counter;
use Google\Cloud\Firestore\Shard;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Firebase distributed counters
 */
class SolutionCountersTest extends TestCase 
{
    use TestTrait;

    const COLLECTION_NAME='Shards_collection';
    const SHARD_NAME='Distributed_counters';

    private static $firestoreProjectId;

    private static $ref;

    public static function setUpBeforeClass()
    {
        require_once __DIR__."/../src/firestore_snippets/SolutionCounters.php";
        
        self::$firestoreProjectId = self::requireEnv('FIRESTORE_PROJECT_ID');

        $db = new FirestoreClient([
            'projectId' => self::$firestoreProjectId
        ]);

        self::$ref=$db->collection(self::COLLECTION_NAME)->document(self::SHARD_NAME);
    }
    
    protected function setUp()
    {
        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
    }


    /**
     * @covers Google\Cloud\Firestore\Counter::getNumShards
     */
    public function testCounter()
    {
        $counter=new Counter(1000);
        $this->assertEquals(1000, $counter->getNumShards());

        $counter2=new Counter(1);
        $this->assertEquals(1, $counter2->getNumShards());
    }
    
    /**
     * @covers Google\Cloud\Firestore\Shard::getCount
     */
    public function testShard()
    {
        $shard = new Shard(1000);
        $this->assertEquals(1000, $shard->getCount());

        $shard2 = new Shard(1);
        $this->assertEquals(1, $shard2->getCount());
    }

    /**
     * @covers Google\Cloud\Firestore\SolutionCounters::initCounter
     * @covers Google\Cloud\Firestore\SolutionCounters::incrementCounter
     * @covers Google\Cloud\Firestore\SolutionCounters::getCount
     */
    public function testSolutionCounters()
    {
        $cnt=5;
        SolutionCounters::initCounter(self::$ref, $cnt);

        $collect = self::$ref->collection(SolutionCounters::SHARD_COLLECT_NAME);
        $docCollection = $collect->documents();
        $docIdList=[];

        foreach ($docCollection as $docSnap) {
            $docIdList[] = $docSnap->id();
        }
        $this->assertEquals($cnt, count($docIdList));

        $this->assertEquals(0, SolutionCounters::getCount(self::$ref));

        SolutionCounters::incrementCounter(self::$ref, $cnt);
        $this->assertEquals(1, SolutionCounters::getCount(self::$ref));

        SolutionCounters::incrementCounter(self::$ref, $cnt);
        $this->assertEquals(2, SolutionCounters::getCount(self::$ref));

        SolutionCounters::incrementCounter(self::$ref, $cnt);
        $this->assertEquals(3, SolutionCounters::getCount(self::$ref));

        foreach ($docIdList as $docId){
            $collect->document($docId)->delete();
        }
    }

    /** Remove SHARD_NAME document
     */
    public static function tearDownAfterClass()
    {
        self::$ref->delete();
    }
}
