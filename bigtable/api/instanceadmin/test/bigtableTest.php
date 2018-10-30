<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;


final class HelloWorldTest extends TestCase
{

    public function testInstanceAdminRun(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';

        $content = $this->runSnippet('run_instance_operations', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $array = explode(PHP_EOL, $content);
        $this->clean_instance($project_id, $instance_id,  $cluster_id);

        $this->assertContains('Instance ' . $instance_id . ' does not exists.', $array);
        $this->assertContains('Creating an Instance:', $array);
        $this->assertContains('Listing Instances:', $array);
        $this->assertContains($instance_id, $array);
        $this->assertContains('Labels: []', $array);
        $this->assertContains('Listing Clusters...', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);
    }

    public function testInstanceAdminDev(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-dev';
        $cluster_id = 'php-cluster-php-dev';

        $content = $this->runSnippet('create_dev_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id,  $cluster_id);

        $this->assertContains('Creating a DEVELOPMENT Instance', $array);
        $this->assertContains('Instance ' . $instance_id . ' does not exists.', $array);
        $this->assertContains('Creating an Instance', $array);
    }
    private function clean_instance($project_id, $instance_id, $cluster_id){

        $this->runSnippet('delete_cluster', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);
        echo "\n";
        echo $project_id."\n";
        echo $instance_id."\n";
        echo $cluster_id."\n";
    }
    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([basename(__FILE__)], $params);
        ob_start();
        require_once __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }

}
