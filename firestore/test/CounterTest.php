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
        require_once __DIR__."/../src/firestore_snippets/Counter.php";
        
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
     * @covers Google\Cloud\Firestore\Counter::__construct
     * @covers Google\Cloud\Firestore\Counter::incrementCounter
     * @covers Google\Cloud\Firestore\Counter::getCount
     */
    public function testCounter()
    {
        $counter = new Counter(self::$ref, 5);

        $collect = self::$ref->collection('SHARDS');
        $docCollection = $collect->documents();

        $docIdList=[];
        foreach ($docCollection as $docSnap) {
            $docIdList[] = $docSnap->id();
        }
        $this->assertEquals(5, count($docIdList));

        $this->assertEquals(0, $counter->getCount(self::$ref));

        $counter->incrementCounter(self::$ref);
        $this->assertEquals(1, $counter->getCount(self::$ref));

        $counter->incrementCounter(self::$ref);
        $this->assertEquals(2, $counter->getCount(self::$ref));

        $counter->incrementCounter(self::$ref);
        $this->assertEquals(3, $counter->getCount(self::$ref));

        foreach ($docIdList as $docId) {
            $collect->document($docId)->delete();
        }
    }
    
    /**
     * @covers Google\Cloud\Firestore\Shard::getCount
     */
    public function testShard()
    {
        $shard = new Shard(7);
        $this->assertEquals(7, $shard->getCount());

        $shard2 = new Shard(2);
        $this->assertEquals(2, $shard2->getCount());
    }

    /** Remove SHARD_NAME document
     */
    public static function tearDownAfterClass()
    {
        self::$ref->delete();
    }
}
