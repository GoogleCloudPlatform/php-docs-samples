<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;


final class BigTableTest extends TestCase
{

    public function testCreateCluster(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-cluster';
        $cluster_id = 'php-sample-cluster-cluster';

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
        $instance_id = 'php-sample-instance-dev';
        $cluster_id = 'php-sample-cluster-dev';

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
        $instance_id = 'php-sample-instance-inter';
        $cluster_id = 'php-sample-cluster-inter';
        $table_id = 'php-sample-table-inter';
        $family_id = 'cf3';

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
        $this->assertContains(sprintf('Created column family %s with Union GC rule.', $family_id), $array);
    }

    public function testCreateFamilyGcMaxAge(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-max-age';
        $cluster_id = 'php-sample-cluster-max-age';
        $table_id = 'php-sample-table-max-age';
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


        $this->assertContains(sprintf('Creating column family %s with MaxAge GC Rule...', $family_id), $array);
        $this->assertContains(sprintf('Created column family %s with MaxAge GC Rule.', $family_id), $array);
    }

    public function testCreateFamilyGcMaxVersions(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-max-ver';
        $cluster_id = 'php-sample-cluster-max-ver';
        $table_id = 'php-sample-table-max-ver';
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
        $instance_id = 'php-sample-instance-gc-nested';
        $cluster_id = 'php-sample-cluster-nested';
        $table_id = 'php-sample-table-nested';
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
        $instance_id = 'php-sample-instance-union';
        $cluster_id = 'php-sample-cluster-union';
        $table_id = 'php-sample-table-union';
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
        $this->assertContains(sprintf('Created column family %s with Union GC rule.', $family_id), $array);
    }

    public function testCreateProductionInstance(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-prod';
        $cluster_id = 'php-sample-cluster-prod';

        $content = $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);
        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains(sprintf('Creating an Instance: %s', $instance_id), $array);
        $this->assertContains(sprintf('Instance %s created.', $instance_id), $array);
    }

    public function testCreateTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-table';
        $cluster_id = 'php-sample-cluster-table';
        $table_id = 'php-sample-table-table';


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
        $instance_id = 'php-sample-instance-cluster';
        $cluster_id = 'php-sample-cluster-cluster';
        $cluster_two_id = 'php-sample-cluster-cluster2';


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
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-delinst';
        $cluster_id = 'php-sample-cluster-delinst';

        $this->runSnippet('create_production_instance', [
            $project_id,
            $instance_id,
            $cluster_id
        ]);

        $content = $this->runSnippet('delete_instance', [
            $project_id,
            $instance_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Deleting Instance', $array);
        $this->assertContains(sprintf('Deleted Instance: %s.', $instance_id), $array);
    }

    public function testDeleteTable(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-deltable';
        $cluster_id = 'php-sample-cluster-deltable';
        $table_id = 'php-sample-table-table';


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

        $this->assertContains(sprintf('Checking if table %s exists...', $table_id), $array);
        $this->assertContains(sprintf('Attempting to delete table %s.', $table_id), $array);
        $this->assertContains(sprintf('Deleted %s table.', $table_id), $array);
    }

    public function testListColumnFamilies(): void
    {
        $project_id = getenv('PROJECT_ID');
        $instance_id = 'php-sample-instance-lfamilies';
        $cluster_id = 'php-sample-cluster-lfamilies';
        $table_id = 'php-sample-table-lfamilies';
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
        $instance_id = 'php-sample-instance-linstance';
        $cluster_id = 'php-sample-cluster-linstance';

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
        $instance_id = 'php-sample-instance-lclusters';
        $cluster_id = 'php-sample-cluster-lclusters';

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
        $instance_id = 'php-sample-insntance-ltable';
        $cluster_id = 'php-sample-cluster-ltable';
        $table_id = 'php-sample-table-ltable';


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
        $instance_id = 'php-sample-instance-updrule';
        $cluster_id = 'php-sample-cluster-updrule';
        $table_id = 'php-sample-table-updrule';
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
