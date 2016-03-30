<?php
/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Test;

use GuzzleHttp\Client;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    const PROJECT_ENV = 'GOOGLE_PROJECT_ID';
    const VERSION_ENV = 'GOOGLE_VERSION_ID';
    const DB_PASSWORD_ENV = 'WP_DB_PASSWORD';

    private static function getTargetDir()
    {
        $tmp = sys_get_temp_dir();
        $e2e_test_version = getenv(self::VERSION_ENV);
        return "$tmp/wp-test-$e2e_test_version";
    }
    public static function setUpBeforeClass()
    {
        $project_id = getenv(self::PROJECT_ENV);
        $e2e_test_version = getenv(self::VERSION_ENV);
        $db_password = getenv(self::DB_PASSWORD_ENV);
        if ($project_id === false) {
            self::fail('Please set ' . self::PROJECT_ENV . ' env var.');
        }
        if ($e2e_test_version === false) {
            self::fail('Please set ' . self::VERSION_ENV . ' env var.');
        }
        if ($db_password === false) {
            self::fail('Please set ' . self::DB_PASSWORD_ENV . ' env var.');
        }
        $helper = __DIR__ . '/../wordpress-helper.php';
        $target = self::getTargetDir();
        $command = "php $helper setup -d $target "
            . " -n -p $project_id "
            . "--db_instance=wp --db_name=wp --db_user=wp "
            . "--db_password=$db_password";
        $wp_url = getenv('WP_DOWNLOAD_URL');
        if ($wp_url !== false) {
            $command .= " --wordpress_url=$wp_url";
        }
        exec($command);
        self::deploy($project_id, $e2e_test_version);
    }

    public static function deploy($project_id, $e2e_test_version)
    {
        $target = self::getTargetDir();
        $cwd = getcwd();
        chdir($target);
        for ($i = 0; $i <= 3; $i++) {
            exec(
                "sh $target/deploy_wrapper.sh gcloud -q preview app deploy "
                . "--version $e2e_test_version "
                . "--project $project_id --no-promote "
                . "$target/app.yaml $target/cron.yaml",
                $output,
                $ret
            );
            if ($ret === 0) {
                chdir($cwd);
                return;
            } else {
                echo 'Retrying deployment';
            }
        }
        chdir($cwd);
        self::fail('Deployment failed.');
    }


    public static function tearDownAfterClass()
    {
        // TODO: check the return value and maybe retry?
        exec('gcloud -q preview app modules delete default --version '
             . getenv(self::VERSION_ENV)
             . ' --project '
             . getenv(self::PROJECT_ENV));
    }

    public function setUp()
    {
        $url = sprintf('https://%s-dot-%s.appspot.com/',
                       getenv(self::VERSION_ENV),
                       getenv(self::PROJECT_ENV));
        $this->client = new Client(['base_uri' => $url]);
    }

    public function testIndex()
    {
        // Index serves succesfully with 'Hello World'.
        // This works because the custom DOCUMENT_ROOT is working.
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'I am very glad that you are testing WordPress instalation.',
            $resp->getBody()->getContents());
    }

    public function testWpadmin()
    {
        // Access to '/wp-admin' and see if it's correctly redirected to
        // /wp-admin/

        // Suppresses following redirect here.
        $resp = $this->client->request(
            'GET', 'wp-admin', ['allow_redirects' => false]);
        $this->assertEquals('301', $resp->getStatusCode(),
                            'wp-admin status code');
        $url = sprintf('https://%s-dot-%s.appspot.com/',
                       getenv(self::VERSION_ENV),
                       getenv(self::PROJECT_ENV));
        $this->assertEquals(
            $url . 'wp-admin/',
            $resp->getHeaderLine('location'));
    }
}
