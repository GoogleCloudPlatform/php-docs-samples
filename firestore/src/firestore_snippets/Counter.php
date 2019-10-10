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

use Google\Cloud\Firestore\DocumentReference;

// [START fs_counter_classes]
/** Counter is a collection of documents (shards)
 * to realize counter with high frequency
 */
class Counter
{
    /**
     * @var int
     */
    private $numShards;
    
    public function __construct(int $numShards)
    {
        $this->numShards = $numShards;
    }
    
    public function getNumShards()
    {
        return $this->numShards;
    }
}


/** 
 * Shard is a single counter, which is used in a group
 * of other shards within Counter.
 *  */
class Shard
{
    /**
     * @var int
     */
    private $count;
    
    public function __construct(int $count)
    {
        $this->count = $count;
    }
    
    public function getCount()
    {
        return $this->count;
    }
}

// [END fs_counter_classes]


class SolutionCounters {
    // [START fs_create_counter]
    /**
     * InitCounter creates a given number of shards as
     * subcollection of specified document.
     * 
     * @param DocumentReference $ref Firestore document
     * @param int $numShards The number of counter fragments. (default 10)
     */
    public static function initCounter(DocumentReference $ref, int $numShards = 10)
    {
        $counter = new Counter($numShards);
        $colRef = $ref->collection('SHARDS');
        for ($i = 0; $i < $counter->getNumShards(); $i++) {
            $doc = $colRef->document($i);
            $doc->set(['Cnt' => 0]);
        }
    }
    // [END fs_create_counter]
    
    // [START fs_increment_counter]
    /**
     * incrementCounter increments a randomly picked shard.
     * 
     * @param DocumentReference $ref Firestore document
     * @param int $numShards The number of counter fragments. (default 10)
     */
    public static function incrementCounter(DocumentReference $ref, int $numShards = 10)
    {
        $colRef = $ref->collection('SHARDS');
        $shardIdx = random_int(0, $numShards-1);
        $doc = $colRef->document($shardIdx);
        $doc->update([
            ['path' => 'Cnt', 'value' => FieldValue::increment(1)]
        ]);
    }
    // [END fs_increment_counter]

    // [START fs_get_count]
    /**
     * getCount returns a total count across all shards.
     * 
     * @param DocumentReference $ref Firestore document
     * @return int
     */
    public static function getCount(DocumentReference $ref)
    {
        $result = 0;
        $docCollection = $ref->collection('SHARDS')->documents();
        foreach ($docCollection as $doc) {
            $result += $doc->data()['Cnt'];
        }
        return $result;
    }
    // [END fs_get_count]
}
