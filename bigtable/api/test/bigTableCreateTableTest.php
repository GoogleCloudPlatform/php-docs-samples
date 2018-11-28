<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

final class BigTableCreateTableTest extends TestCase
{
    const INSTANCE_ID_PREFIX = 'php-itest-';
    const CLUSTER_ID_PREFIX = 'php-ctest-';
    
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

        $tableAdminClient = new BigtableTableAdminClient();
        $tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);
        
        $this->checkTable($tableAdminClient, $tableName);
        $this->clean_instance($project_id, $instance_id, $cluster_id);
    }

    private function checkTable($tableAdminClient, $tableName)
    {
        try {
            $table = $tableAdminClient->GetTable($tableName);
            $this->assertEquals($table->getName(), $tableName);
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
