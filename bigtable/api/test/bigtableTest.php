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
    static $listInstances = [];

    public static function setUpBeforeClass()
    {
        self::$instanceAdminClient = new BigtableInstanceAdminClient();
        self::$tableAdminClient = new BigtableTableAdminClient();
    }
    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }

    public static function tearDownAfterClass()
    {
        try {
            self::runSnippet('delete_instance', [
                self::$projectId,
                $listInstance
            ]);
            unset(self::$listInstances[$key]);
        } catch (ApiException $e) {
            printf('Failed to delete instance "%s"' . PHP_EOL, $listInstance);
        }
    }
    
    public function testCreateCluster()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $content = $this->runSnippet('create_cluster', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);
        $array = explode(PHP_EOL, $content);
        
        $clusterName = self::$instanceAdminClient->clusterName(self::$projectId, $instance_id, $cluster_id);

        $this->check_cluster($clusterName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateDevInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = $this->runSnippet('create_dev_instance', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);
        $array = explode(PHP_EOL, $content);
        
        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->check_instance($instanceName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testListInstances()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);
        
        $content = $this->runSnippet('list_instance', [
            self::$projectId,
            $instance_id
        ]);

        $array = explode(PHP_EOL, $content);
        
        $this->assertContains('Listing Instances:', $array);
        $this->assertContains($instance_id, $array);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testListTable()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('list_tables', [
            self::$projectId,
            $instance_id
        ]);
        $this->clean_instance(self::$projectId, $instance_id);
        $array = explode(PHP_EOL, $content);
        
        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . $instance_id . '/tables/' . $table_id, $array);
        
    }

    public function testCreateFamilyGcIntersection()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_intersection', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

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
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateFamilyGcMaxAge()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_age', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

        $gcRuleCompare = [
            'gcRule' => [
                'maxAge' => '432000.000000000s'
            ]
        ];
        
        $this->check_rule($tableName, 'cf1', $gcRuleCompare);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateFamilyGcMaxVersions()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_versions', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

        $gcRuleCompare = [
            'gcRule' => [
                'maxNumVersions' => 2
            ]
        ];

        $this->check_rule($tableName, 'cf2', $gcRuleCompare);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateFamilyGcNested()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_nested', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

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
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateFamilyGcUnion()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_union', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

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
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testCreateProductionInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = $this->runSnippet('create_production_instance', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->check_instance($instanceName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testcreate_table()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $this->runSnippet('create_table', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

        $this->check_table($tableName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testDeleteCluster()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $cluster_two_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $clusterName = self::$instanceAdminClient->clusterName(self::$projectId, $instance_id, $cluster_two_id);

        $this->runSnippet('create_cluster', [
            self::$projectId,
            $instance_id,
            $cluster_two_id,
            'us-east1-c'
        ]);

        $this->check_cluster($clusterName);

        $content = $this->runSnippet('delete_cluster', [
            self::$projectId,
            $instance_id,
            $cluster_two_id
        ]);

        try {
            $cluster = self::$instanceAdminClient->GetCluster($clusterName);
            $this->fail(sprintf('Cluster %s still exists', $cluster->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testDeleteInstance()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $this->check_instance($instanceName);

        $content = $this->runSnippet('delete_instance', [
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

    public function testDeleteTable()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $tableName = self::$tableAdminClient->tableName(self::$projectId, $instance_id, $table_id);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $this->runSnippet('create_table', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $this->check_table($tableName);

        $content = $this->runSnippet('delete_table', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        try {
            $table = self::$tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
            $this->fail(sprintf('Instance %s still exists', $table->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
        $this->clean_instance(self::$projectId, $instance_id);
    }

    public function testListColumnFamilies()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, $instance_id, $cluster_id, $table_id);

        $this->runSnippet('create_family_gc_union', [
            self::$projectId,
            $instance_id,
            $table_id
        ]);

        $content = $this->runSnippet('list_column_families', [
            self::$projectId,
            $instance_id,
            $table_id,
        ]);
        $this->clean_instance(self::$projectId, $instance_id);
        $array = explode(PHP_EOL, $content);
        
        $this->assertContains(sprintf('Column Family: %s', 'cf3'), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":"432000.000000000s"}]}}}', $array);
    }

    public function testListInstanceClusters()
    {
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->create_production_instance(self::$projectId,$instance_id,$cluster_id);

        $content = $this->runSnippet('list_instance_clusters', [
            self::$projectId,
            $instance_id
        ]);
        $this->clean_instance(self::$projectId, $instance_id);
        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);
    }

    private function create_production_instance($project_id,$instance_id,$cluster_id)
    {
        self::$listInstances[] = $instance_id;
        $content = $this->runSnippet('create_production_instance', [
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
        $this->create_production_instance($project_id,$instance_id,$cluster_id);

        $this->runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);
    }

    private function clean_instance($project_id, $instance_id)
    {
        $content = $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $testFunc = function() use ($sampleName, $params) {
            $argv = array_merge([basename(__FILE__)], $params);
            ob_start();
            require __DIR__ . "/../src/$sampleName.php";
            return ob_get_clean();
        };

        return self::$backoff->execute($testFunc);
    }
}