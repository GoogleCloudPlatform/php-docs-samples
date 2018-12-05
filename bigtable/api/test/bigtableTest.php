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

        self::create_production_instance(self::$projectId,self::$instanceId,self::$clusterId);
    }
    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }
    
    public function testCreateCluster()
    {
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        
        $content = self::runSnippet('create_cluster', [
            self::$projectId,
            self::$instanceId,
            $cluster_id
        ]);
        $array = explode(PHP_EOL, $content);
        
        $clusterName = self::$instanceAdminClient->clusterName(self::$projectId, self::$instanceId, $cluster_id);

        $this->check_cluster($clusterName);
    }

    public function testCreateDevInstance()
    {
        $instance_id = uniqid(INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(CLUSTER_ID_PREFIX);

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

    public function testCreateProductionInstance()
    {
        $instance_id = uniqid(INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(CLUSTER_ID_PREFIX);

        $content = self::runSnippet('create_production_instance', [
            self::$projectId,
            $instance_id,
            $cluster_id
        ]);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, $instance_id);

        $this->check_instance($instanceName);
        $this->clean_instance(self::$projectId, $instance_id);
    }

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

    public function testListColumnFamilies()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instance_id, self::$clusterId, $tableId);

        self::runSnippet('create_family_gc_union', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $content = self::runSnippet('list_column_families', [
            self::$projectId,
            self::$instanceId,
            $table_id,
        ]);
        $this->clean_instance(self::$projectId, self::$instanceId);
        $array = explode(PHP_EOL, $content);
        
        $this->assertContains(sprintf('Column Family: %s', 'cf3'), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":"432000.000000000s"}]}}}', $array);
    }

    public function testListInstanceClusters()
    {
        $content = self::runSnippet('list_instance_clusters', [
            self::$projectId,
            self::$instanceId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . self::$projectId . '/instances/' . self::$instanceId . '/clusters/' . self::$cluster_id, $array);
    }

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

    public function testCreateFamilyGcNested()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->create_table(self::$projectId, self::$instanceId, self::$clusterd, $tableId);

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

    private static function create_production_instance($project_id,$instance_id,$cluster_id)
    {
        self::runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
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

        return self::$backoff->execute($testFunc);
    }
}