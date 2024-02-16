<?php
/**
 * Copyright 2023 Google Inc.
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

namespace Google\Cloud\Samples\Datastore;

use DateTime;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\EntityInterface;
use Google\Cloud\Datastore\EntityIterator;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\GqlQuery;
use Google\Cloud\Datastore\Query\Query;

// [START datastore_cursor_paging]
/**
 * Fetch a query cursor.
 *
 * @param DatastoreClient $datastore
 * @param int $pageSize
 * @param string $pageCursor
 * @return array
 */
function cursor_paging(DatastoreClient $datastore, int $pageSize, string $pageCursor = '')
{
    $query = $datastore->query()
        ->kind('Task')
        ->limit($pageSize)
        ->start($pageCursor);
    $result = $datastore->runQuery($query);
    $nextPageCursor = '';
    $entities = [];
    /* @var Entity $entity */
    foreach ($result as $entity) {
        $nextPageCursor = $entity->cursor();
        $entities[] = $entity;
    }

    printf("Found %s entities", count($entities));

    $entities = [];
    if(!empty($nextPageCursor)) {
        $query = $datastore->query()
          ->kind('Task')
          ->limit($pageSize)
          ->start($nextPageCursor);
        $result = $datastore->runQuery($query);

        foreach ($result as $entity) {
            $entities[] = $entity;
        }

        printf("Found %s entities with next page cursor", count($entities));
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
