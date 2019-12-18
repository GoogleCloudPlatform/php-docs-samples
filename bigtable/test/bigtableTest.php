<?php

namespace Google\Cloud\Samples\Bigtable\Tests;

use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use PHPUnit\Framework\TestCase;

final class BigtableTest extends TestCase
{
    use BigtableTestTrait;

    const INSTANCE_ID_PREFIX = 'php-instance-';
    const CLUSTER_ID_PREFIX = 'php-cluster-';
    const TABLE_ID_PREFIX = 'php-table-';

    private static $clusterId;

    public static function setUpBeforeClass()
    {
        self::setUpBigtableVars();
    }

    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }

    public function testCreateProductionInstance()
    {
        self::$instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        self::$clusterId = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_production_instance', [
            self::$projectId,
            self::$instanceId,
            self::$clusterId
        ]);

        $instanceName = self::$instanceAdminClient->instanceName(
            self::$projectId,
            self::$instanceId
        );

        $this->checkInstance($instanceName);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateAndDeleteCluster()
    {
        // Create a new cluster as last cluster in an instance cannot be deleted
        $clusterId = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_cluster', [
            self::$projectId,
            self::$instanceId,
            $clusterId,
            'us-east1-c'
        ]);
        $array = explode(PHP_EOL, $content);

        $clusterName = self::$instanceAdminClient->clusterName(
            self::$projectId,
            self::$instanceId,
            $clusterId
        );

        $this->checkCluster($clusterName);

        $content = self::runSnippet('delete_cluster', [
            self::$projectId,
            self::$instanceId,
            $clusterId
        ]);

        try {
            self::$instanceAdminClient->getCluster($clusterName);
            $this->fail(sprintf('Cluster %s still exists', $cluster->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
    }

    public function testCreateDevInstance()
    {
        $instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        $clusterId = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_dev_instance', [
            self::$projectId,
            $instanceId,
            $clusterId
        ]);
        $array = explode(PHP_EOL, $content);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instanceId);

        $this->checkInstance($instanceName);
        $this->cleanInstance(self::$projectId, $instanceId);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testListInstances()
    {
        $content = self::runSnippet('list_instance', [
            self::$projectId,
            self::$instanceId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Instances:', $array);
        $this->assertContains(self::$instanceId, $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testListTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('list_tables', [
            self::$projectId,
            self::$instanceId
        ]);
        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . self::$instanceId . '/tables/' . $tableId, $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testListColumnFamilies()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        self::runSnippet('create_family_gc_union', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $content = self::runSnippet('list_column_families', [
            self::$projectId,
            self::$instanceId,
            $tableId,
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains(sprintf('Column Family: %s', 'cf3'), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":"432000s"}]}}}', $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testListInstanceClusters()
    {
        $content = self::runSnippet('list_instance_clusters', [
            self::$projectId,
            self::$instanceId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . self::$instanceId . '/clusters/' . self::$clusterId, $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        self::runSnippet('create_table', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $this->checkTable($tableName);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateFamilyGcUnion()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_union', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'union' => [
                    'rules' => [
                        [
                            'maxNumVersions' => 2
                        ],
                        [
                            'maxAge' => '432000s'
                        ]
                    ]
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf3', $gcRuleCompare);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateFamilyGcNested()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_nested', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'union' => [
                    'rules' => [
                        [
                            'maxNumVersions' => 10
                        ],
                        [
                            'intersection' => [
                                'rules' => [
                                    [
                                        'maxAge' => '2592000s'
                                    ],
                                    [
                                        'maxNumVersions' => 2
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf5', $gcRuleCompare);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateFamilyGcMaxVersions()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_max_versions', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'maxNumVersions' => 2
            ]
        ];

        $this->checkRule($tableName, 'cf2', $gcRuleCompare);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateFamilyGcMaxAge()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_max_age', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'maxAge' => '432000s'
            ]
        ];

        $this->checkRule($tableName, 'cf1', $gcRuleCompare);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateFamilyGcIntersection()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_intersection', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'intersection' => [
                    'rules' => [
                        [
                            'maxAge' => '432000s'
                        ],
                        [
                            'maxNumVersions' => 2
                        ]
                    ]
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf4', $gcRuleCompare);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testDeleteTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);
        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);
        $this->checkTable($tableName);

        $content = self::runSnippet('delete_table', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        try {
            $table = self::$tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
            $this->fail(sprintf('Instance %s still exists', $table->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testHelloWorld()
    {
        $this->requireGrpc();

        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $content = self::runSnippet('hello_world', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains(sprintf('Creating a Table: %s', $tableId), $array);
        $this->assertContains(sprintf('Created table %s', $tableId), $array);
        $this->assertContains('Writing some greetings to the table.', $array);
        $this->assertContains('Getting a single greeting by row key.', $array);
        $this->assertContains('Hello World!', $array);
        $this->assertContains('Scanning for all greetings:', $array);
        $this->assertContains('Hello World!', $array);
        $this->assertContains('Hello Cloud Bigtable!', $array);
        $this->assertContains('Hello PHP!', $array);
        $this->assertContains(sprintf('Deleted %s table.', $tableId), $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testDeleteInstance()
    {
        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, self::$instanceId);

        $content = self::runSnippet('delete_instance', [
            self::$projectId,
            self::$instanceId
        ]);

        try {
            $instance = self::$instanceAdminClient->getInstance($instanceName);
            $this->fail(sprintf('Instance %s still exists', $instance->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
    }

    private function checkCluster($clusterName)
    {
        try {
            $cluster = self::$instanceAdminClient->getCluster($clusterName);
            $this->assertEquals($cluster->getName(), $clusterName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            } else {
                throw $e;
            }
        }
    }

    private function checkRule($tableName, $familyKey, $gcRuleCompare)
    {
        try {
            $table = self::$tableAdminClient->getTable($tableName);
            $columnFamilies = $table->getColumnFamilies()->getIterator();
            $key = $columnFamilies->key();
            $json = $columnFamilies->current()->serializeToJsonString();

            $gcRule = json_decode($columnFamilies->current()->serializeToJsonString(), true);

            $this->assertEquals($key, $familyKey);
            $this->assertEquals($gcRule, $gcRuleCompare);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            } else {
                throw $e;
            }
        }
    }

    private function checkInstance($instanceName)
    {
        try {
            $instance = self::$instanceAdminClient->getInstance($instanceName);
            $this->assertEquals($instance->getName(), $instanceName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            } else {
                throw $e;
            }
        }
    }

    private function checkTable($tableName)
    {
        try {
            $table = self::$tableAdminClient->getTable($tableName);
            $this->assertEquals($table->getName(), $tableName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            } else {
                throw $e;
            }
        }
    }

    private function createTable($projectId, $instanceId, $clusterId, $tableId)
    {
        self::runSnippet('create_table', [
            $projectId,
            $instanceId,
            $tableId
        ]);
    }

    private function cleanInstance($projectId, $instanceId)
    {
        $content = self::runSnippet('delete_instance', [
            $projectId,
            $instanceId
        ]);
    }
}
