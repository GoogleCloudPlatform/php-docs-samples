<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

final class BigTableCreateFamilyGcNestedTest extends TestCase
{
	public function testCreateFamilyGcNested(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-gc-nested';
        $cluster_id = 'php-sample-cluster-nested';
        $table_id = 'php-sample-table-nested';
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_nested', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        
        $tableAdminClient = new BigtableTableAdminClient();
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        try{
            $table = $tableAdminClient->getTable($tableName);
            $columnFamilies = $table->getColumnFamilies()->getIterator();
            $key = $columnFamilies->key();
            $gcRule = json_decode($columnFamilies->current()->serializeToJsonString(),true);
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

            $this->assertEquals($key, 'cf5');
            $this->assertEquals($gcRule, $gcRuleCompare);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(),true);
                $this->fail($error['message']);
            }
            throw $e;
        }
        $this->clean_instance($project_id, $instance_id, $cluster_id);
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