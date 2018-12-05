<?php
namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\Cloud\TestUtils\TestTrait;
use Google\ApiCore\ApiException;

final class BigTableTest extends TestCase
{
    use TestTrait,ExponentialBackoffTrait;

    const INSTANCE_ID_PREFIX = 'php-instance-';
    const CLUSTER_ID_PREFIX = 'php-cluster-';
    const TABLE_ID_PREFIX = 'php-table-';
    static $instanceAdminClient;
    static $tableAdminClient;
    static $instanceId;
    static $clusterId;
    static $clusterTwoId;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVarBeforeClass();
        self::$instanceAdminClient = new BigtableInstanceAdminClient();
        self::$tableAdminClient = new BigtableTableAdminClient();

        self::$instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        self::$clusterId = uniqid(self::CLUSTER_ID_PREFIX);
        self::$clusterTwoId = uniqid(self::CLUSTER_ID_PREFIX);
    }
    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }
    
    public function testCreateProdution()
    {
        self::create_production_instance(self::$projectId,self::$instanceId,self::$clusterId);
    }
    
    /**
     * @depends testCreateProdution
     */
    public function testCreateCluster()
    {
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_cluster', [
            self::$projectId,
            self::$instanceId,
            $cluster_id,
            'us-east1-c'
        ]);
        $array = explode(PHP_EOL, $content);
        
        $clusterName = self::$instanceAdminClient->clusterName(self::$projectId, self::$instanceId, $cluster_id);

        $this->check_cluster($clusterName);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateDevInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_dev_instance', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);
        $array = explode(PHP_EOL, $content);
        
        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->check_instance($instanceName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateProductionInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_production_instance', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->check_instance($instanceName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    /**
     * @depends testCreateProdution
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
     * @depends testCreateProdution
     */
    public function testListTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('list_tables', [
            self::$projectId,
            self::$instanceId
        ]);
        $array = explode(PHP_EOL, $content);
        
        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . self::$instanceId . '/tables/' . $tableId, $array);
    }

    /**
     * @depends testCreateProdution
     */
    public function testListColumnFamilies()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

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
        $this->clean_instance(self::$projectId, self::$instanceId);
        $array = explode(PHP_EOL, $content);
        
        $this->assertContains(sprintf('Column Family: %s', 'cf3'), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":"432000.000000000s"}]}}}', $array);
    }

    /**
     * @depends testCreateProdution
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
     * @depends testCreateProdution
     */
    public function testcreate_table()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        self::runSnippet('create_table', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $this->check_table($tableName);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateFamilyGcUnion()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

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
                            'maxAge' => '432000.000000000s'
                        ]
                    ]
                ]
            ]
        ];

        $this->check_rule($tableName, 'cf3', $gcRuleCompare);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateFamilyGcNested()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

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
                                        'maxAge' => '2592000.000000000s'
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
        
        $this->check_rule($tableName, 'cf5', $gcRuleCompare);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateFamilyGcMaxVersions()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

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

        $this->check_rule($tableName, 'cf2', $gcRuleCompare);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateFamilyGcMaxAge()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runSnippet('create_family_gc_max_age', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $gcRuleCompare = [
            'gcRule' => [
                'maxAge' => '432000.000000000s'
            ]
        ];
        
        $this->check_rule($tableName, 'cf1', $gcRuleCompare);
    }

    /**
     * @depends testCreateProdution
     */
    public function testCreateFamilyGcIntersection()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

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
                            'maxAge' => '432000.000000000s'
                        ],
                        [
                            'maxNumVersions' => 2
                        ]
                    ]
                ]
            ]
        ];
        
        $this->check_rule($tableName, 'cf4', $gcRuleCompare);
    }

    /**
     * @depends testCreateProdution
     */
    public function testDeleteCluster()
    {
        $clusterName = self::$instanceAdminClient->clusterName(self::$projectId, self::$instanceId, self::$clusterTwoId);

        self::runSnippet('create_cluster', [
            self::$projectId,
            self::$instanceId,
            self::$clusterTwoId,
            'us-east1-c'
        ]);

        $this->check_cluster($clusterName);

        $content = self::runSnippet('delete_cluster', [
            self::$projectId,
            self::$instanceId,
            self::$clusterTwoId
        ]);

        try {
            $cluster = self::$instanceAdminClient->GetCluster($clusterName);
            $this->fail(sprintf('Cluster %s still exists', $cluster->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @depends testCreateProdution
     */
    public function testDeleteInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        

        $this->check_instance($instanceName);

        $content = self::runSnippet('delete_instance', [
            self::$projectId,
            $instance_id
        ]);

        try {
            $instance = self::$instanceAdminClient->GetInstance($instanceName);
            $this->fail(sprintf('Instance %s still exists', $instance->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
        $this->clean_instance(self::$projectId, $instance_id);
    }

    /**
     * @depends testCreateProdution
     */
    public function testDeleteTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, self::$instanceId, $tableId);

        $this->create_production_instance(self::$projectId,self::$instanceId,self::$clusterId);

        self::runSnippet('create_table', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $this->check_table($tableName);

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

    private static function create_production_instance($project_id,$instance_id,$cluster_id)
    {
        $content = self::runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
    }

    private function check_cluster($clusterName)
    {
        try {
            $cluster = self::$instanceAdminClient->GetCluster($clusterName);
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

    private function check_rule($tableName, $familyKey, $gcRuleCompare)
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

    private function check_instance($instanceName)
    {
        try {
            $instance = self::$instanceAdminClient->GetInstance($instanceName);
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

    private function check_table($tableName)
    {
        try {
            $table = self::$tableAdminClient->GetTable($tableName);
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

    private function create_table($project_id, $instance_id, $cluster_id, $table_id)
    {
        self::runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);
    }

    private function clean_instance($project_id, $instance_id)
    {
        $content = self::runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);
    }

    private static function runSnippet($sampleName, $params = [])
    {
        $testFunc = function() use ($sampleName, $params) {
            $argv = array_merge([basename(__FILE__)], $params);
            ob_start();
            require __DIR__ . "/../src/$sampleName.php";
            return ob_get_clean();
        };

        if (self::$backoff) {
            return self::$backoff->execute($testFunc);
        }
        return $testFunc();
    }
}