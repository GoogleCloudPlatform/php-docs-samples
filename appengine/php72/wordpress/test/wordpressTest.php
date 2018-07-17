<?php
/**
 * Copyright 2018 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\AppEngine\Php72\WordPress;
use Google\Cloud\TestUtils\ExecuteCommandTrait;

class wordpressTest extends \PHPUnit_Framework_TestCase
{
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../wordpress.php';

    public function testCreate()
    {
        // these variables aren't actually taken into account, as we are just
        // testing the files get generated appropriately.
        $projectId = 'test-project-id';
        $dbName = 'wordpress-db';
        $dbUser = 'wordpress-user';
        $dbPassword = 'test-db-password';
        $dbInstance = 'test-db-instance';
        $dir = sprintf('%s/wp-gae-php72-%s', sys_get_temp_dir(), time());
        $this->runCommand('create', [
            '--dir' => $dir,
            '--project_id' => $projectId,
            '--db_name'     => $dbName,
            '--db_user'     => $dbUser,
            '--db_password' => $dbPassword,
            '--db_instance' => $dbInstance,
        ]);

        $this->assertTrue(is_dir($dir));
        $files = ['app.yaml', 'wp-config.php'];
        foreach ($files as $file) {
            $this->assertFileExists($dir . '/' . $file);
        }
        // check the syntax of the rendered PHP file
        passthru(sprintf('php -l %s/wp-config.php', $dir), $ret);
        $this->assertEquals(0, $ret);

        // check naively that variables were added
        $wpConfig = file_get_contents($dir . '/wp-config.php');
        $this->assertContains($projectId, $wpConfig);
        $this->assertContains($dbPassword, $wpConfig);
    }

    public function testUpdate()
    {
        $dir = sprintf('%s/wp-update-%s', sys_get_temp_dir(), time());
        mkdir($dir);
        $this->assertTrue(is_dir($dir));

        // these variables aren't actually taken into account, as we are just
        // testing the files get generated appropriately.
        $projectId  = 'test-updated-project-id';
        $dbName = 'wordpress-db';
        $dbUser = 'wordpress-user';
        $dbPassword = 'test-db-password';
        $dbInstance = 'test-db-instance';
        $this->runCommand('update', [
            'dir' => $dir,
            '--project_id' => $projectId,
            '--db_name'     => $dbName,
            '--db_user'     => $dbUser,
            '--db_password' => $dbPassword,
            '--db_instance' => $dbInstance,
        ]);

        $files = ['app.yaml', 'wp-config.php'];
        foreach ($files as $file) {
            $this->assertFileExists($dir . '/' . $file);
        }
        // check the syntax of the rendered PHP file
        passthru(sprintf('php -l %s/wp-config.php', $dir), $ret);
        $this->assertEquals(0, $ret);

        // check naively that variables were added
        $wpConfig = file_get_contents($dir . '/wp-config.php');
        $this->assertContains($projectId, $wpConfig);
        $this->assertContains($dbPassword, $wpConfig);
    }
}
