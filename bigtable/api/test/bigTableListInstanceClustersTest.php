<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

final class BigTableListFamiliesTest extends TestCase
{
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

        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);

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