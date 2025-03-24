<?php
/**
 * Copyright 2025 Google LLC
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

namespace Google\Cloud\Samples\Spanner;

use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
 */
class spannerProtoTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    use RetryTrait, EventuallyConsistentTestTrait;

    /** @var string $instanceId */
    protected static $instanceId;

    /** @var string $databaseId */
    protected static $databaseId;

    /** @var Instance $instance */
    protected static $instance;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$instanceId = 'proto-test-' . time() . rand();
        self::$databaseId = 'proto-db-' . time() . rand();
        self::$instance = $spanner->instance(self::$instanceId);

        // Create the instance for testing
        $operation = $spanner->createInstance(
            $spanner->instanceConfiguration('regional-us-central1'),
            self::$instanceId,
            [
                'displayName' => 'Proto Test Instance',
                'nodeCount' => 1,
                'labels' => [
                    'cloud_spanner_samples' => true,
                ]
            ]
        );
        $operation->pollUntilComplete();
    }

    public function testCreateDatabaseWithProtoColumns()
    {
        $output = $this->runAdminFunctionSnippet('create_database_with_proto_columns', [
            self::$projectId,
            self::$instanceId,
            self::$databaseId
        ]);

        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString(sprintf('Created database %s on instance %s', self::$databaseId, self::$instanceId), $output);
    }

    /**
     * @depends testCreateDatabaseWithProtoColumns
     */
    public function testInsertDataWithProtoColumns()
    {
        $output = $this->runFunctionSnippet('insert_data_with_proto_columns', [
            self::$instanceId,
            self::$databaseId,
            1 // User ID
        ]);

        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertDataWithProtoColumns
     */
    public function testQueryDataWithProtoColumns()
    {
        $output = $this->runFunctionSnippet('query_data_with_proto_columns', [
            self::$instanceId,
            self::$databaseId,
            1 // User ID
        ]);

        $this->assertStringContainsString('User:', $output);
        $this->assertStringContainsString('Test User 1', $output);
        $this->assertStringContainsString('Book:', $output);
        $this->assertStringContainsString('testing.data.Book', $output);
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_values($params) ?: [self::$instanceId, self::$databaseId]
        );
    }

    private function runAdminFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_values($params) ?: [self::$projectId, self::$instanceId, self::$databaseId]
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$instance->exists()) {
            // Clean up database
            $database = self::$instance->database(self::$databaseId);
            if ($database->exists()) {
                $database->drop();
            }
            self::$instance->delete();
        }
    }
}
