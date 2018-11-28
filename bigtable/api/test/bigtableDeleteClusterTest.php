<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;

final class BigTableDeleteClusterTest extends TestCase
{
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

        $instanceAdminClient = new BigtableInstanceAdminClient();
        $clusterName = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_two_id);

        $this->runSnippet('create_cluster', [
            $project_id,
            $instance_id,
            $cluster_two_id,
            'us-east1-c'
        ]);

        $this->check_cluster($instanceAdminClient, $clusterName);
        
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

    private function check_cluster($instanceAdminClient, $clusterName)
    {
        try {
            $cluster = $instanceAdminClient->GetCluster($clusterName);
            $this->assertEquals($clusterName = $cluster->getName(), $clusterName);
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