<?php
declare(strict_types=1);

namespace Google\Cloud\Samples\BigTable\Tests;

use PHPUnit\Framework\TestCase;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\BigtableClient;
use Google\ApiCore\ApiException;

final class BigTableTest extends TestCase
{
    const INSTANCE_ID_PREFIX = 'php-itest-';
    const CLUSTER_ID_PREFIX = 'php-ctest-';
    const TABLE_ID_PREFIX = 'php-ttest-';
    static $project_id;
    static $instanceAdminClient;
    static $tableAdminClient;
    static $dataClient;
    
    public static function setUpBeforeClass()
    {
        $keyFilePath = getenv('GOOGLE_CLOUD_PHP_TESTS_KEY_PATH');
        $keyFileData = json_decode(file_get_contents($keyFilePath), true);

        self::$project_id = $keyFileData['project_id'];
        self::$instanceAdminClient = new BigtableInstanceAdminClient();
        self::$tableAdminClient = new BigtableTableAdminClient();
        self::$dataClient = new BigtableClient([
            'projectId' => self::$project_id
        ]);
    }

    public function testGettingRow(): void
    {
        $project_id = self::$project_id;
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);
        
        $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $this->runSnippet('writing_rows',[
            $project_id,
            $instance_id,
            $table_id
        ]);

        $content = $this->runSnippet('getting_a_row', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        $array = explode(PHP_EOL, $content);
        
        $this->clean_instance($project_id, $instance_id, $cluster_id);
        
        $this->assertContains('Getting a single greeting by row key.', $array);
        $this->assertContains('Hello World!', $array);
    }

    public function testQuickstart(): void
    {
        $project_id = self::$project_id;
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);
        
        $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        $dataClient = self::$dataClient;

        $table = $dataClient->table(
            $instance_id,
            $table_id
        );

        $insertRows = [
            'rk5' => [
                'cf1' => [
                    'cq5' => [
                        'value' => "Value5",
                        'timeStamp' => $this->time_in_microseconds()
                    ]
                ]
            ]
        ];

        $table->upsert($insertRows);

        $content = $this->runSnippet('quickstart', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        
        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Row key: rk5', $array);
        $this->assertContains('Data: Value5', $array);
    }

    public function testScanningAllRows(): void
    {
        $project_id = self::$project_id;
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);
        
        $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $this->runSnippet('writing_rows',[
            $project_id,
            $instance_id,
            $table_id
        ]);

        $content = $this->runSnippet('scanning_all_rows', [
            $project_id,
            $instance_id,
            $table_id
        ]);
        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);

        $this->assertContains('Scanning for all greetings:', $array);
        $this->assertContains('Hello World!', $array);
        $this->assertContains('Hello Cloud Bigtable!', $array);
        $this->assertContains('Hello PHP!', $array);
    }
    
    public function testWritingRows(): void
    {
        $project_id = self::$project_id;
        $instance_id = uniqid(self::INSTANCE_ID_PREFIX);
        $cluster_id = uniqid(self::CLUSTER_ID_PREFIX);
        $table_id = uniqid(self::TABLE_ID_PREFIX);

        $this->createTable($project_id, $instance_id, $cluster_id, $table_id);
        
        $this->runSnippet('create_family_gc_max_age', [
            $project_id,
            $instance_id,
            $table_id
        ]);

        $content = $this->runSnippet('writing_rows',[
            $project_id,
            $instance_id,
            $table_id
        ]);

        $array = explode(PHP_EOL, $content);

        $this->clean_instance($project_id, $instance_id, $cluster_id);
        
        $this->assertContains('Writing some greetings to the table.', $array);
        $this->assertContains('Creating Row with value: Hello World! in column greeting.', $array);
        $this->assertContains('Creating Row with value: Hello Cloud Bigtable! in column greeting.', $array);
        $this->assertContains('Creating Row with value: Hello PHP! in column greeting.', $array);
    }
    
    private function time_in_microseconds(){
        $mt = microtime(true);
        $mt = sprintf('%.03f',$mt);
        return (float)$mt*1000000;
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
