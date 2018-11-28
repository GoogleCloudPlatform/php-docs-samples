<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use Google\ApiCore\ApiException;
use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;

final class BigTableDeleteInstanceTest extends TestCase
{
    public function testDeleteInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);

        $instanceAdminClient = new BigtableInstanceAdminClient();
        $instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $this->check_instance($instanceAdminClient, $instanceName);

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

    private function checkInstance($instanceAdminClient, $instanceName)
    {
        try {
            $instance = $instanceAdminClient->GetInstance($instanceName);
            $this->assertEquals($instance->getName(), 'projects/' . $project_id . '/instances/' . $instance_id);
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
