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
    public function testTableAdminRun(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-php-prod';
        $table_id = 'quickstart-table-test-create';
        $content = $this->runSnippet('run_table_operations', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        $array = explode(PHP_EOL, $content);

        $this->assertContains('Checking if table '.$table_id.' exists', $array);
        $this->assertContains('Creating the '.$table_id.' table', $array);
        $this->assertContains('Created table '.$table_id, $array);
        $this->assertContains('projects/'.$project_id.'/instances/'.$instance_id.'/tables/'.$table_id, $array);
        $this->assertContains('Creating column family cf1 with MaxAge GC Rule...', $array);
        $this->assertContains('Created column family cf1 with MaxAge GC Rule.', $array);
        $this->assertContains('Creating column family cf2 with max versions GC rule...', $array);
        $this->assertContains('Created column family cf2 with Max Versions GC Rule.', $array);
        $this->assertContains('Creating column family cf3 with union GC rule...', $array);
        $this->assertContains('Created column family cf3 with Union GC rule', $array);
        $this->assertContains('Creating column family cf4 with Intersection GC rule...', $array);
        $this->assertContains('Created column family cf4 with Union GC rule', $array);
        $this->assertContains('Creating column family cf5 with a Nested GC rule...', $array);
        $this->assertContains('Created column family cf5 with a Nested GC rule.', $array);
        $this->assertContains('Column Family: cf3', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":{"seconds":432000}}]}}}', $array);
        $this->assertContains('Column Family: cf5', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":10},{"intersection":{"rules":[{"maxAge":{"seconds":2592000}},{"maxNumVersions":2}]}}]}}}', $array);
        $this->assertContains('Column Family: cf4', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"intersection":{"rules":[{"maxAge":{"seconds":432000}},{"maxNumVersions":2}]}}}', $array);
        $this->assertContains('Column Family: cf1', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"maxAge":{"seconds":432000}}}', $array);
        $this->assertContains('Column Family: cf2', $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"maxNumVersions":2}}', $array);
        $this->assertContains('Print column family cf1 GC rule before update...', $array);
        $this->assertContains('Column Family: cf1', $array);
        $this->assertContains('{"gcRule":{"maxAge":{"seconds":432000}}}', $array);
        $this->assertContains('Updating column family cf1 GC rule...', $array);
        $this->assertContains('Print column family cf1 GC rule after update...', $array);
        $this->assertContains('Column Family: cf1{"gcRule":{"maxNumVersions":1}}', $array);
        $this->assertContains('Delete a column family cf2...', $array);
        $this->assertContains('Column family cf2 deleted successfully.', $array);


        $content = $this->runSnippet('delete_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Checking if table '.$table_id.' exists...', $array);
        $this->assertContains('Table '.$table_id.' exists.', $array);
        $this->assertContains('Deleting '.$table_id.' table.', $array);
        $this->assertContains('Deleted '.$table_id.' table.', $array);
    }
    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([basename(__FILE__)], $params);
        ob_start();
        require_once __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }

}
