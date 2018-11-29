<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\ApiCore\ApiException;

final class BigTableTest extends TestCase
{
    const INSTANCE_ID_PREFIX = 'php-itest-';
    const CLUSTER_ID_PREFIX = 'php-ctest-';
    const TABLE_ID_PREFIX = 'php-ttest-';
    private $instanceAdminClient;
    private $tableAdminClient;
    protected function setUpBeforeClass()
    {
        $this->instanceAdminClient = new BigtableInstanceAdminClient();
        $this->tableAdminClient = new BigtableTableAdminClient();
    }

    public function testCreateCluster(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('create_cluster', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $instanceAdminClient = $this->instanceAdminClient;
        $clusterName = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_id);
        
        $this->check_cluster($instanceAdminClient, $clusterName);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateDevInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = $this->runSnippet('create_dev_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $instanceAdminClient = $this->tableAdminClient;
        $instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
        
        $this->check_instance($instanceName);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateFamilyGcIntersection(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_intersection', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $gcRuleCompare = [
            'gcRule' => [
                'intersection' => [
                    'rules' => [
                        [
                            'maxAge' => [
                                'seconds' => 432000
                            ]
                        ],
                        [
                            'maxNumVersions' => 2
                        ]
                    ]
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf4', $gcRuleCompare);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateFamilyGcMaxAge(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $gcRuleCompare = [
            'gcRule' => [
                'maxAge' => [
                    'seconds' => 432000
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf1', $gcRuleCompare);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateFamilyGcMaxVersions(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_versions', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $gcRuleCompare = [
            'gcRule' => [
                'maxNumVersions' => 2
            ]
        ];

        $this->checkRule($tableName, 'cf2', $gcRuleCompare);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateFamilyGcNested(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_nested', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
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
                                        'maxAge' => [
                                            'seconds' => 2592000
                                        ]
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
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testCreateFamilyGcUnion(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $gcRuleCompare = [
            'gcRule' => [
                'union' => [
                    'rules' => [
                        [
                            'maxNumVersions' => 2
                        ],
                        [
                            'maxAge' => [
                                'seconds' => 432000
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->checkRule($tableName, 'cf3', $gcRuleCompare);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testCreateProductionInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $content = $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $instanceAdminClient = $this->instanceAdminClient;
        $instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
        
        $this->check_instance($instanceName);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testCreateTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $this->runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $this->checkTable($tableName);
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testDeleteCluster(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $cluster_two_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $instanceAdminClient = $this->instanceAdminClient;
        $clusterName = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_two_id);

        $this->runSnippet('create_cluster', [
            $project_id,
            $instance_id,
            $cluster_two_id,
            'us-east1-c'
        ]);

        $this->check_cluster($clusterName);
        
        $content = $this->runSnippet('delete_cluster', [
            $project_id,
            $instance_id,
            $cluster_two_id
        ]);
        
        try {
            $cluster = $instanceAdminClient->GetCluster($clusterName);
            $this->fail(sprintf('Cluster %s still exists', $cluster->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testDeleteInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $instanceAdminClient = $this->instanceAdminClient;
        $instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $this->check_instance($instanceName);

        $content = $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);

        try {
            $instance = $instanceAdminClient->GetInstance($instanceName);
            $this->fail(sprintf('Instance %s still exists', $instance->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testDeleteTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $tableAdminClient = $this->tableAdminClient;
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $this->runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $this->checkTable($tableName);

        $content = $this->runSnippet('delete_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        try {
            $table = $tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
            $this->fail(sprintf('Instance %s still exists', $table->getName()));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    public function testListColumnFamilies(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);
        
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $content = $this->runSnippet('list_column_families', [
            $project_id,
            $instance_id,
            $table_id,
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains(sprintf('Column Family: %s', 'cf3'), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":{"seconds":432000}}]}}}', $array);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testListInstanceClusters(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('list_instance_clusters', [
            $project_id,
            $instance_id
        ]);

        $array = explode(PHP_EOL, $content);
        print_r($array);
        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testListInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('list_instance', [
            $project_id,
            $instance_id
        ]);

        $array = explode(PHP_EOL, $content);
        print_r($array);
        $this->assertContains('Listing Instances:', $array);
        $this->assertContains($instance_id, $array);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    public function testListTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('list_tables', [
            $project_id,
            $instance_id
        ]);
        
        $array = explode(PHP_EOL, $content);
        print_r($array);
        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/tables/' . $table_id, $array);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }
    
    private function check_cluster($clusterName)
    {
        $instanceAdminClient = $this->instanceAdminClient;
        try {
            $cluster = $instanceAdminClient->GetCluster($clusterName);
            $this->assertEquals($cluster->getName(), $clusterName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            }else{
                throw $e;    
            }
        }
    }
    
    private function checkRule($tableName, $familyKey, $gcRuleCompare)
    {
        $tableAdminClient = $this->tableAdminClient;
        try {
            $table = $tableAdminClient->getTable($tableName);
            $columnFamilies = $table->getColumnFamilies()->getIterator();
            $key = $columnFamilies->key();
            $gcRule = json_decode($columnFamilies->current()->serializeToJsonString(), true);

            $this->assertEquals($key, $familyKey);
            $this->assertEquals($gcRule, $gcRuleCompare);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            }else{
                throw $e;    
            }
        }
    }

    private function check_instance($instanceName)
    {
        $instanceAdminClient = $this->instanceAdminClient;
        try {
            $instance = $instanceAdminClient->GetInstance($instanceName);
            $this->assertEquals($instance->getName(), $instanceName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            }else{
                throw $e;    
            }
        }
    }
    
    private function checkTable($tableName)
    {
        $tableAdminClient = $this->tableAdminClient;
        try {
            $table = $tableAdminClient->GetTable($tableName);
            $this->assertEquals($table->getName(), $tableName);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            }else{
                throw $e;    
            }
        }
    }

    private function createTable($project_id, $instance_id, $cluster_id, $table_id)
    {
        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $this->runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);
    }

    private function clean_instance($project_id, $instance_id, $cluster_id)
    {
        $content = $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([basename(__FILE__)], $params);
        ob_start();
        require_once __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }

}
