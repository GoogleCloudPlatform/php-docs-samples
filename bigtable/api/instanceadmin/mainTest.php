<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelloWorldTest extends TestCase
{
	public function testInstanceAdminRun(): void {
        ob_start();
        run_instance_operations( getenv('PROJECT_ID') , 'php-test-instance' );
        $content = ob_get_contents();
		ob_end_clean();
        $array = explode("\n", $content);

        $this->assertContains('Instance quickstart-instance-php2 does not exists.',$array);
        $this->assertContains('Creating an Instance:',$array);
        $this->assertContains('Listing Instances:',$array);
        $this->assertContains('cpp-integration-tests',$array);
        $this->assertContains('DotNet perf 2',$array);
        $this->assertContains('endurance',$array);
        $this->assertContains('Integration Tests it-2y9m5hcz',$array);
        $this->assertContains('Integration Tests it-41ck9ga1',$array);
        $this->assertContains('Integration Tests it-4uc394o8',$array);
        $this->assertContains('Integration Tests it-g1vj71p2',$array);
        $this->assertContains('Integration Tests it-g85mo5xk',$array);
        $this->assertContains('Integration Tests it-gwony5v2',$array);
        $this->assertContains('Integration Tests it-jehn2gt6',$array);
        $this->assertContains('Integration Tests it-mil8fuwg',$array);
        $this->assertContains('Integration Tests it-usapuda3',$array);
        $this->assertContains('Integration Tests it-uxf7orsk',$array);
        $this->assertContains('node-client-performance',$array);
        $this->assertContains('quickstart-instance-php',$array);
        $this->assertContains('quickstart-instance-php2',$array);
        $this->assertContains('Shared Perf',$array);
        $this->assertContains('Shared Perf 2',$array);
        $this->assertContains('test-sangram-beam',$array);
        $this->assertContains('test-sumit',$array);
        $this->assertContains('vt-instance',$array);
        $this->assertContains('Name of instance: vt-instance',$array);
        $this->assertContains('Labels: []',$array);
        $this->assertContains('Listing Clusters...',$array);
        $this->assertContains('projects/grass-clump-479/instances/quickstart-instance-php2/clusters/php-cluster',$array);
    }
    public function testInstanceAdminDev(): void {
        ob_start();
        create_dev_instance( getenv('PROJECT_ID') , 'php-test-instance' , 'php-cluster' );
        $content = ob_get_contents();
		ob_end_clean();
        $array = explode("\n", $content);

        $this->assertContains('Creating a DEVELOPMENT Instance',$array);
        $this->assertContains('Instance quickstart-instance-php2 does not exists.',$array);
        $this->assertContains('Creating an Instance',$array);
    }
    public function testInstanceAdminDeleteInstance(): void {
        ob_start();
        delete_instance( getenv('PROJECT_ID') , 'php-test-instance' );
        $content = ob_get_contents();
		ob_end_clean();
        $array = explode("\n", $content);

        $this->assertContains('Deleting Instance',$array);
        $this->assertContains('Deleted Instance: quickstart-instance-php2.',$array);
    }
    public function testInstanceAdminAddCluster(): void {
        ob_start();
        add_cluster( getenv('PROJECT_ID') , 'quickstart-instance-php' , 'php-cluster-d' );
        $content = ob_get_contents();
		ob_end_clean();
        $array = explode("\n", $content);

        $this->assertContains('Adding Cluster to Instance quickstart-instance-php2',$array);
        $this->assertContains('Listing Clusters...',$array);
        $this->assertContains('projects/grass-clump-479/instances/quickstart-instance-php2/clusters/php-cluster-d2',$array);
        $this->assertContains('Cluster not created, as php-cluster-d2',$array);
        
    }
    public function testInstanceAdminDeleteCluster(): void {
        ob_start();
        delete_cluster( getenv('PROJECT_ID') , 'quickstart-instance-php' , 'php-cluster-d' );
        $content = ob_get_contents();
		ob_end_clean();
        $array = explode("\n", $content);

        $this->assertContains('Deleting Cluster',$array);
        $this->assertContains('Cluster deleted: php-cluster-d2',$array);
    }
    
}
