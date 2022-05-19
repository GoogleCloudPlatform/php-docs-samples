<?php

namespace Google\Cloud\Samples\Bigtable\Tests;

use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use PHPUnit\Framework\TestCase;

final class BigtableTest extends TestCase
{
    use BigtableTestTrait;

    public const CLUSTER_ID_PREFIX = 'php-cluster-';
    public const INSTANCE_ID_PREFIX = 'php-instance-';
    public const TABLE_ID_PREFIX = 'php-table-';
    public const APP_PROFILE_ID_PREFIX = 'php-app-profile-';
    public const SERVICE_ACCOUNT_ID_PREFIX = 'php-sa-';    // Shortened due to length constraint b/w 6 and 30.

    private static $autoscalingClusterId;
    private static $clusterId;
    private static $appProfileId;
    private static $serviceAccountId;
    private static $serviceAccountEmail;
    private static $policyRole;

    public static function setUpBeforeClass(): void
    {
        self::setUpBigtableVars();
    }

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff();
    }

    public function testCreateProductionInstance()
    {
        self::$autoscalingClusterId = uniqid(self::CLUSTER_ID_PREFIX);
        self::$clusterId = uniqid(self::CLUSTER_ID_PREFIX);
        self::$instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        self::$appProfileId = uniqid(self::APP_PROFILE_ID_PREFIX);

        $content = self::runFunctionSnippet('create_production_instance', [
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
    public function testGetInstance()
    {
        $content = self::runFunctionSnippet('get_instance', [
            self::$projectId,
            self::$instanceId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Display Name: ' . self::$instanceId, $array);
    }

    /**
     * @depends testGetInstance
     */
    public function testUpdateInstance()
    {
        $updatedName = uniqid(self::INSTANCE_ID_PREFIX);
        $content = self::runFunctionSnippet('update_instance', [
            self::$projectId,
            self::$instanceId,
            $updatedName
        ]);

        $expectedResponse = "Instance updated with the new display name: $updatedName." . PHP_EOL;

        $this->assertSame($expectedResponse, $content);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateAppProfile()
    {
        $content = self::runFunctionSnippet('create_app_profile', [
            self::$projectId,
            self::$instanceId,
            self::$clusterId,
            self::$appProfileId
        ]);
        $array = explode(PHP_EOL, $content);

        $appProfileName = self::$instanceAdminClient->appProfileName(self::$projectId, self::$instanceId, self::$appProfileId);

        $this->assertContains('AppProfile created: ' . $appProfileName, $array);

        $this->checkAppProfile($appProfileName);
    }

    /**
     * @depends testCreateAppProfile
     */
    public function testGetAppProfile()
    {
        $content = self::runFunctionSnippet('get_app_profile', [
            self::$projectId,
            self::$instanceId,
            self::$appProfileId
        ]);
        $array = explode(PHP_EOL, $content);

        $appProfileName = self::$instanceAdminClient->appProfileName(self::$projectId, self::$instanceId, self::$appProfileId);

        $this->assertContains('Name: ' . $appProfileName, $array);
    }

    /**
     * @depends testGetAppProfile
     */
    public function testListAppProfiles()
    {
        $content = self::runFunctionSnippet('list_app_profiles', [
            self::$projectId,
            self::$instanceId
        ]);
        $array = explode(PHP_EOL, $content);

        $appProfileName = self::$instanceAdminClient->appProfileName(self::$projectId, self::$instanceId, self::$appProfileId);

        $this->assertContains('Name: ' . $appProfileName, $array);
    }

    /**
     * @depends testGetAppProfile
     */
    public function testUpdateAppProfile()
    {
        $content = self::runFunctionSnippet('update_app_profile', [
            self::$projectId,
            self::$instanceId,
            self::$clusterId,
            self::$appProfileId
        ]);
        $array = explode(PHP_EOL, $content);

        $appProfileName = self::$instanceAdminClient->appProfileName(
            self::$projectId,
            self::$instanceId,
            self::$appProfileId
        );

        $this->assertContains('App profile updated: ' . $appProfileName, $array);

        // let's check if the allow_transactional_writes also changed
        $appProfile = self::$instanceAdminClient->getAppProfile($appProfileName);

        $this->assertTrue($appProfile->getSingleClusterRouting()->getAllowTransactionalWrites());
    }

    /**
     * @depends testCreateAppProfile
     */
    public function testDeleteAppProfile()
    {
        $content = self::runFunctionSnippet('delete_app_profile', [
            self::$projectId,
            self::$instanceId,
            self::$appProfileId
        ]);
        $array = explode(PHP_EOL, $content);

        $appProfileName = self::$instanceAdminClient->appProfileName(self::$projectId, self::$instanceId, self::$appProfileId);

        $this->assertContains('App Profile ' . self::$appProfileId . ' deleted.', $array);

        // let's check if we can fetch the profile or not
        try {
            self::$instanceAdminClient->getAppProfile($appProfileName);
            $this->fail(sprintf('App Profile %s still exists', self::$appProfileId));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateAndDeleteCluster()
    {
        // Create a new cluster as last cluster in an instance cannot be deleted
        $clusterId = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runFunctionSnippet('create_cluster', [
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

        $content = self::runFunctionSnippet('delete_cluster', [
            self::$projectId,
            self::$instanceId,
            $clusterId
        ]);

        try {
            self::$instanceAdminClient->getCluster($clusterName);
            $this->fail(sprintf('Cluster %s still exists', $clusterName));
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateClusterWithAutoscaling()
    {
        $content = self::runFunctionSnippet('create_cluster_autoscale_config', [
          self::$projectId,
          self::$instanceId,
          self::$autoscalingClusterId,
          'us-east1-c'
        ]);

        // get the cluster name created with above id
        $clusterName = self::$instanceAdminClient->clusterName(
            self::$projectId,
            self::$instanceId,
            self::$autoscalingClusterId,
        );

        $this->checkCluster($clusterName);
        $this->assertStringContainsString(sprintf(
            'Cluster created: %s',
            self::$autoscalingClusterId,
        ), $content);
    }

    /**
     * @depends testCreateClusterWithAutoscaling
     */
    public function testUpdateClusterWithAutoscaling()
    {
        // Update autoscale config in cluster
        $content = self::runFunctionSnippet('update_cluster_autoscale_config', [
            self::$projectId,
            self::$instanceId,
            self::$autoscalingClusterId,
        ]);

        $this->assertStringContainsString(sprintf(
            'Cluster %s updated with autoscale config.',
            self::$autoscalingClusterId,
        ), $content);
    }

    /**
     * @depends testCreateClusterWithAutoscaling
     */
    public function testDisableAutoscalingInCluster()
    {
        $numNodes = 2;

        // Disable autoscale config in cluster
        $content = self::runFunctionSnippet('disable_cluster_autoscale_config', [
            self::$projectId,
            self::$instanceId,
            self::$autoscalingClusterId,
            $numNodes
        ]);

        $this->assertStringContainsString(sprintf(
            'Cluster updated with the new num of nodes: %s.',
            $numNodes,
        ), $content);
    }

    public function testCreateDevInstance()
    {
        $instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        $clusterId = uniqid(self::CLUSTER_ID_PREFIX);

        $content = self::runFunctionSnippet('create_dev_instance', [
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
        $content = self::runFileSnippet('list_instance', [
            self::$projectId
        ]);

        $array = explode(PHP_EOL, $content);

        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, self::$instanceId);

        $this->assertContains('Listing Instances:', $array);
        $this->assertContains($instanceName, $array);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testListTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable(self::$projectId, self::$instanceId, self::$clusterId, $tableId);

        $content = self::runFileSnippet('list_tables', [
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

        self::runFunctionSnippet('create_family_gc_union', [
            self::$projectId,
            self::$instanceId,
            $tableId
        ]);

        $content = self::runFileSnippet('list_column_families', [
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
        $content = self::runFileSnippet('list_instance_clusters', [
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
    public function testGetCluster()
    {
        $content = self::runFunctionSnippet('get_cluster', [
            self::$projectId,
            self::$instanceId,
            self::$clusterId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Name: projects/' . self::$projectId . '/instances/' . self::$instanceId . '/clusters/' . self::$clusterId, $array);
    }

    /**
     * @depends testGetCluster
     */
    public function testUpdateCluster()
    {
        $newNumNodes = 2;

        $content = self::runFunctionSnippet('update_cluster', [
            self::$projectId,
            self::$instanceId,
            self::$clusterId,
            $newNumNodes
        ]);

        $expectedResponse = "Cluster updated with the new num of nodes: $newNumNodes." . PHP_EOL;

        $this->assertSame($expectedResponse, $content);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testCreateTable()
    {
        $tableId = uniqid(self::TABLE_ID_PREFIX);

        self::runFunctionSnippet('create_table', [
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

        $content = self::runFunctionSnippet('create_family_gc_union', [
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

        $content = self::runFunctionSnippet('create_family_gc_nested', [
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

        $content = self::runFunctionSnippet('create_family_gc_max_versions', [
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

        $content = self::runFunctionSnippet('create_family_gc_max_age', [
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

        $content = self::runFunctionSnippet('create_family_gc_intersection', [
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

        $content = self::runFunctionSnippet('delete_table', [
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

        $content = self::runFileSnippet('hello_world', [
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
    public function testSetIamPolicy()
    {
        self::$policyRole = 'roles/bigtable.user';
        self::$serviceAccountId = uniqid(self::SERVICE_ACCOUNT_ID_PREFIX);
        self::$serviceAccountEmail = $this->createServiceAccount(self::$serviceAccountId);

        $user = 'serviceAccount:' . self::$serviceAccountEmail;
        $content = self::runFunctionSnippet('set_iam_policy', [
            self::$projectId,
            self::$instanceId,
            $user,
            self::$policyRole
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains(self::$policyRole . ':' . $user, $array);
    }

    /**
    * @depends testSetIamPolicy
    */
    public function testGetIamPolicy()
    {
        $user = 'serviceAccount:' . self::$serviceAccountEmail;

        $content = self::runFunctionSnippet('get_iam_policy', [
            self::$projectId,
            self::$instanceId
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains(self::$policyRole . ':' . $user, $array);

        // cleanup
        $this->deleteServiceAccount(self::$serviceAccountEmail);
    }

    /**
     * @depends testCreateProductionInstance
     */
    public function testDeleteInstance()
    {
        $instanceName = self::$instanceAdminClient->instanceName(self::$projectId, self::$instanceId);

        $content = self::runFunctionSnippet('delete_instance', [
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

    private function checkAppProfile($appProfileName)
    {
        try {
            $appProfile = self::$instanceAdminClient->getAppProfile($appProfileName);
            $this->assertEquals($appProfile->getName(), $appProfileName);
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
        self::runFunctionSnippet('create_table', [
            $projectId,
            $instanceId,
            $tableId
        ]);
    }

    private function cleanInstance($projectId, $instanceId)
    {
        $content = self::runFunctionSnippet('delete_instance', [
            $projectId,
            $instanceId
        ]);
    }
}
