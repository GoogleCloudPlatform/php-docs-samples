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
require __DIR__ . '/../util.php';

class utilTest extends PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected static $bigquery;
    protected static $projectId;
    protected static $shakespeareQuery;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        if (self::$hasCredentials) {
            self::$bigquery = createAuthorizedClient();
            self::$projectId = getenv('GOOGLE_PROJECT_ID');
            self::$shakespeareQuery =
                'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
                'FROM [publicdata:samples.shakespeare]';
        }
    }

    public function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
    }

    public function isShakespeare($rows)
    {
        $foundKingLear = false;
        $foundHamlet = false;
        foreach ($rows as $row) {
            foreach ($row['f'] as $field) {
                $foundHamlet = $foundHamlet || $field['v'] == 'hamlet';
                $foundKingLear = $foundKingLear || $field['v'] == 'kinglear';
            }
        }

        return $foundHamlet && $foundKingLear;
    }

    public function testSyncQuery()
    {
        $rows = SyncQuery(
            self::$bigquery,
            self::$projectId,
            self::$shakespeareQuery
        );
        $this->assertTrue($this->isShakespeare($rows));
    }

    public function testSyncQueryTimeout()
    {
        $rows = SyncQuery(
            self::$bigquery,
            self::$projectId,
            self::$shakespeareQuery,
            1
        );
        $this->assertNull($rows);
    }

    public function testGetRows()
    {
        $request = new Google_Service_Bigquery_QueryRequest();
        $request->setQuery(self::$shakespeareQuery);
        $request->setMaxResults(3);
        $query = self::$bigquery->jobs->query(self::$projectId, $request);
        $this->assertTrue($query->getJobComplete());
        $rows = getRows(
            self::$bigquery,
            self::$projectId,
            $query->getJobReference()->getJobId(),
            3  // Only 3 rows at a time, please.
        );
        $this->assertTrue($this->isShakespeare($rows));
    }

    public function testAsyncQuery()
    {
        $job = AsyncQuery(
            self::$bigquery,
            self::$projectId,
            self::$shakespeareQuery
        );
        $job = pollJob(
            self::$bigquery,
            $job->getJobReference()->getProjectId(),
            $job->getJobReference()->getJobId(),
            2000  // Check status every 2 seconds.
        );
        $rows = getRows(
            self::$bigquery,
            $job->getJobReference()->getProjectId(),
            $job->getJobReference()->getJobId()
        );
        $this->assertTrue($this->isShakespeare($rows));
    }

    public function testListDatasets()
    {
        $datasets = listDatasets(self::$bigquery, self::$projectId);
        echo 'Datasets for ' . self::$projectId . ':';
        foreach ($datasets as $dataset) {
            echo $dataset->getFriendlyName();
        }
        echo '';
    }

    public function testListProjects()
    {
        $projects = listProjects(self::$bigquery);
        echo 'Projects:';
        foreach ($projects as $project) {
            echo $project->getFriendlyName();
        }
        $this->assertGreaterThan(0, count($projects));
    }
}
