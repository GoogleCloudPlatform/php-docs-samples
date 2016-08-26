<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Cloud\Samples\BigQuery;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Table;

/**
 * Unit Tests for BrowseTableCommand.
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testBigQueryClient()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }

        $bigQuery = require __DIR__ . '/../src/functions/bigquery_client.php';

        $this->assertInstanceOf(BigQueryClient::class, $bigQuery);
    }
    public function testGetTable()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$datasetId = getenv('GOOGLE_BIGQUERY_DATASET')) {
            $this->markTestSkipped('No bigquery dataset name');
        }
        if (!$tableId = getenv('GOOGLE_BIGQUERY_TABLE')) {
            $this->markTestSkipped('No bigquery table name');
        }

        $table = BigQuery\get_table($projectId, $datasetId, $tableId);

        $this->assertInstanceOf(Table::class, $table);
    }
}
