<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

final class BigTableUpdateGcRuleTest extends TestCase
{
    public function testUpdateGcRule(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-instance-updrule';
        $cluster_id = 'php-cluster-updrule';
        $table_id = 'php-table-updrule';
        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $tableAdminClient = new BigtableTableAdminClient();
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        try {
            $table = $tableAdminClient->getTable($tableName);
            $columnFamilies = $table->getColumnFamilies()->getIterator();
            $key = $columnFamilies->key();
            $gcRule = json_decode($columnFamilies->current()->serializeToJsonString(), true);

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
            $this->assertEquals($key, 'cf3');
            $this->assertEquals($gcRule, $gcRuleCompare);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
                $this->fail($error['message']);
            }
            throw $e;
        }

        $content = $this->runSnippet('update_gc_rule', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        try {
            $table = $tableAdminClient->getTable($tableName);
            $columnFamilies = $table->getColumnFamilies()->getIterator();
            $key = $columnFamilies->key();
            $gcRule = json_decode($columnFamilies->current()->serializeToJsonString(), true);

            $gcRuleCompare = [
                'gcRule' => [
                    'maxNumVersions' => 1
                ]
            ];
            $this->assertEquals($key, 'cf3');
            $this->assertEquals($gcRule, $gcRuleCompare);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $error = json_decode($e->getMessage(), true);
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

    private function clean_instance($project_id, $instance_id, $cluster_id = null)
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