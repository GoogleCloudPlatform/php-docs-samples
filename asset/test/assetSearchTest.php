<?php
/**
 * Copyright 2020 Google Inc.
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

namespace Google\Cloud\Samples\Asset;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

/**
 * Unit Tests for asset search commands.
 *
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
 */
class assetSearchTest extends TestCase
{
    use RetryTrait;
    use TestTrait;

    private static $datasetId;
    private static $dataset;

    public static function setUpBeforeClass(): void
    {
        $client = new BigQueryClient([
            'projectId' => self::$projectId,
        ]);
        self::$datasetId = sprintf('temp_dataset_%s', time());
        self::$dataset = $client->createDataset(self::$datasetId);
    }

    public static function tearDownAfterClass(): void
    {
        self::$dataset->delete();
    }

    public function testSearchAllResources()
    {
        $scope = 'projects/' . self::$projectId;
        $query = 'name:' . self::$datasetId;

        $output = $this->runFunctionSnippet('search_all_resources', [
            $scope,
            $query
        ]);

        $this->assertStringContainsString(self::$datasetId, $output);
    }

    public function testSearchAllIamPolicies()
    {
        $scope = 'projects/' . self::$projectId;
        $query = 'policy:roles/owner';

        $output = $this->runFunctionSnippet('search_all_iam_policies', [
            $scope,
            $query
        ]);
        $this->assertStringContainsString(self::$projectId, $output);
    }
}
