<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

final class BigTableListTableTest extends TestCase
{
    public function testListTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-insntance-ltable';
        $cluster_id = 'php-sample-cluster-ltable';
        $table_id = 'php-sample-table-ltable';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('list_tables', [
            $project_id,
            $instance_id
        ]);
        
        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/tables/' . $table_id, $array);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    private function checkRule($tableAdminClient, $tableName, $familyKey, $gcRuleCompare)
    {
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
            }
            throw $e;
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