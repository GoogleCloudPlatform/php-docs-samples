<?php
/**
 * Copyright 2018 Google LLC.
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

namespace Google\Cloud\Samples\BigQuery\Tests;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

final class HelloWorldTest extends TestCase
{
    public function testTableAdminRun(): void
    {
        $content = $this->runSnippet('run_table_operations', [
            getenv('PROJECT_ID'),
            'quickstart-instance-php2',
            'quickstart-instance-table'
        ]);
        $array = explode("\n", $content);

        $this->assertContains('Checking if table quickstart-instance-table exists', $array);
        $this->assertContains('Creating the quickstart-instance-table table', $array);
        $this->assertContains('Created table quickstart-instance-table', $array);
        $this->assertContains('projects/grass-clump-479/instances/quickstart-instance-php/tables/quickstart-instance-table', $array);
        $this->assertContains('projects/grass-clump-479/instances/quickstart-instance-php/tables/table-test', $array);
        $this->assertContains('Creating column family cf1 with MaxAge GC Rule...', $array);
        $this->assertContains('Created column family cf1 with MaxAge GC Rule.', $array);
        $this->assertContains('Creating column family cf2 with max versions GC rule...', $array);
        $this->assertContains('Created column family cf2 with Max Versions GC Rule.', $array);
        $this->assertContains('Creating column family cf3 with union GC rule...', $array);
        $this->assertContains('Created column family cf3 with Union GC rule', $array);
        $this->assertContains('Creating column family cf4 with Intersection GC rule...', $array);
        $this->assertContains('Created column family cf4 with Union GC rule', $array);
        $this->assertContains('Creating column family cf5 with a Nested GC rule...', $array);
        $this->assertContains('Created column family cf5 with a Nested GC rule.', $array);
        $this->assertContains('Column Family: cf3', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":{"seconds":432000}}]}}}', $array);
        $this->assertContains('Column Family: cf5', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":10},{"intersection":{"rules":[{"maxAge":{"seconds":2592000}},{"maxNumVersions":2}]}}]}}}', $array);
        $this->assertContains('Column Family: cf4', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"intersection":{"rules":[{"maxAge":{"seconds":432000}},{"maxNumVersions":2}]}}}', $array);
        $this->assertContains('Column Family: cf1', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"maxAge":{"seconds":432000}}}', $array);
        $this->assertContains('Column Family: cf2', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"maxNumVersions":2}}', $array);
        $this->assertContains('Print column family cf1 GC rule before update...', $array);
        $this->assertContains('Column Family: cf1', $array);
        $this->assertContains('{"gcRule":{"maxAge":{"seconds":432000}}}', $array);
        $this->assertContains('Updating column family cf1 GC rule...', $array);
        $this->assertContains('Print column family cf1 GC rule after update...', $array);
        $this->assertContains('Column Family: cf1{"gcRule":{"maxNumVersions":1}}', $array);
        $this->assertContains('Delete a column family cf2...', $array);
        $this->assertContains('Column family cf2 deleted successfully.', $array);

    }

    public function testTableAdminDelete(): void
    {
        $content = $this->runSnippet('delete_table', [
            getenv('PROJECT_ID'),
            'quickstart-instance-php2',
            'quickstart-instance-table'
        ]);
        $array = explode("\n", $content);

        $this->assertContains('Checking if table quickstart-instance-table exists...', $array);
        $this->assertContains('Table quickstart-instance-table exists.', $array);
        $this->assertContains('Deleting quickstart-instance-table table.', $array);
        $this->assertContains('Deleted quickstart-instance-table table.', $array);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId], $params);
        ob_start();
        require __DIR__ . "/src/$sampleName.php";
        return ob_get_clean();
    }
}
