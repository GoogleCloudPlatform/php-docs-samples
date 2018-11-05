<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;


final class BigTableTest extends TestCase
{
    public function testCreateCluster(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('create_cluster', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Adding Cluster to Instance ' . $instance_id, $array);
        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);
        $this->assertContains('Cluster ' . $cluster_id . ' not created', $array);
    }

    public function testCreateDevInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';

        $content = $this->runSnippet('create_dev_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Creating a DEVELOPMENT Instance', $array);
        $this->assertContains('Creating an Instance: ' . $instance_id, $array);
        $this->assertContains('Instance ' . $instance_id . ' created.', $array);
    }

    public function testCreateFamilyGcIntersection(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = '';

        $this->createTable($project_id, $instance_id, $table_id);

        $content = $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        print_r($array);
    }

    public function testCreateFamilyGcMaxAge(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf1';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Creating column family ' . $family_id . ' with MaxAge GC Rule...', $array);
        $this->assertContains('Created column family ' . $family_id . ' with MaxAge GC Rule.', $array);
    }

    public function testCreateFamilyGcMaxVersions(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf2';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_max_versions', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Creating column family ' . $family_id . ' with max versions GC rule...', $array);
        $this->assertContains('Created column family ' . $family_id . ' with Max Versions GC Rule.', $array);
    }

    public function testCreateFamilyGcNested(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf5';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_nested', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Creating column family ' . $family_id . ' with a Nested GC rule...', $array);
        $this->assertContains('Created column family ' . $family_id . ' with a Nested GC rule.', $array);
    }

    public function testCreateFamilyGcUnion(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf5';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $content = $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains(sprintf('Creating column family %s with union GC rule...', $family_id), $array);
        $this->assertContains(sprintf('Created column family cf5 with Union GC rule.', $family_id), $array);
    }

    public function testCreateProductionInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';

        $content = $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Creating an Instance:', $array);
        $this->assertContains('Instance ' . $instance_id . ' created.', $array);
    }

    public function testCreateTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';
        $table_id = 'table-php-prod';


        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $content = $this->runSnippet('create_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Checking if table ' . $table_id . ' exists', $array);
        $this->assertContains('Creating the ' . $table_id . ' table', $array);
        $this->assertContains('Created table ' . $table_id, $array);
    }

    public function testDeleteCluster(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';
        $cluster_two_id = 'php-cluster-php-prod-two';


        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $this->runSnippet('create_cluster', [
            $project_id,
            $instance_id,
            $cluster_two_id,
            'us-east1-d'
        ]);
        $content = $this->runSnippet('delete_cluster', [
            $project_id,
            $instance_id,
            $cluster_two_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Deleting Cluster', $array);
        $this->assertContains('Cluster ' . $cluster_two_id . ' deleted.', $array);
    }

    public function testDeleteInstance(): void
    {
        $this->runSnippet('delete_cluster', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);
    }

    public function testDeleteTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';
        $table_id = 'table-php-prod';


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
        $content = $this->runSnippet('delete_table', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Checking if table table-php-prod exists...', $array);
        $this->assertContains('Attempting to delete table ' . $table_id . '.', $array);
        $this->assertContains('Deleted ' . $table_id . ' table.', $array);
    }

    public function testListColumnFamilies(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf5';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);

        $content = $this->runSnippet('list_column_families', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains(sprintf('Column Family: %s', $family_id), $array);
        $this->assertContains('GC Rule:', $array);
        $this->assertContains('{"gcRule":{"union":{"rules":[{"maxNumVersions":2},{"maxAge":{"seconds":432000}}]}}}', $array);
    }

    public function testListInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('list_instance', [
            $project_id,
            $instance_id
        ]);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Instances:', $array);
        $this->assertContains($instance_id, $array);
    }

    public function testListInstanceClusters(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('list_instance_clusters', [
            $project_id,
            $instance_id
        ]);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $array = explode(PHP_EOL, $content);

        $this->assertContains('Listing Clusters:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/clusters/' . $cluster_id, $array);
    }

    public function testListTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod';
        $table_id = 'table-php-prod';


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
        $content = $this->runSnippet('list_tables', [
            $project_id,
            $instance_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Listing Tables:', $array);
        $this->assertContains('projects/' . $project_id . '/instances/' . $instance_id . '/tables/' . $table_id, $array);
    }

    public function testUpdateGcRule(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'quickstart-instance-php-prod';
        $cluster_id = 'php-cluster-php-prod-second';
        $table_id = 'php-table-prod';
        $family_id = 'cf5';

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);

        $this->runSnippet('create_family_gc_union', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);
        $content = $this->runSnippet('update_gc_rule', [
            $project_id,
            $instance_id,
            $table_id,
            $family_id
        ]);
        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains(sprintf('Updating column family %s GC rule...', $family_id), $array);
        $this->assertContains(sprintf('Print column family %s GC rule after update...', $family_id), $array);
        $this->assertContains(sprintf('Column Family: %s{"gcRule":{"maxNumVersions":1}}', $family_id), $array);
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
        echo $project_id . "\n";
        echo $instance_id . "\n";
        echo $cluster_id . "\n";
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([basename(__FILE__)], $params);
        ob_start();
        require_once __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }

}
