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

class StdTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    const PROJECT_ENV = 'GOOGLE_PROJECT_ID';
    const VERSION_ENV = 'GOOGLE_VERSION_ID';
    const DB_PASSWORD_ENV = 'WP_DB_PASSWORD';

    private static function getVersion()
    {
        return "wp-std-" . getenv(self::VERSION_ENV);
    }

    private static function getTargetDir()
    {
        $tmp = sys_get_temp_dir();
        $e2e_test_version = self::getVersion();
        return "$tmp/$e2e_test_version";
    }
    public static function setUpBeforeClass()
    {
        if (getenv('RUN_DEPLOYMENT_TESTS') !== 'true') {
            self::markTestSkipped(
                'To run this test, set RUN_DEPLOYMENT_TESTS env to true.'
            );
        }
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
            . " -n -p $project_id --env=s "
            . "--db_instance=wp-std --db_name=wp --db_user=root "
            . "--db_password=$db_password";
        $wp_url = getenv('WP_DOWNLOAD_URL');
        if ($wp_url !== false) {
            $command .= " --wordpress_url=$wp_url";
        }
        exec($command);
        self::deploy($project_id, self::getVersion());
    }

    public static function deploy($project_id, $e2e_test_version)
    {
        $target = self::getTargetDir();
        for ($i = 0; $i <= 3; $i++) {
            exec(
                "gcloud -q app deploy "
                . "--version $e2e_test_version "
                . "--project $project_id --no-promote "
                . "$target/app.yaml $target/cron.yaml",
                $output,
                $ret
            );
            if ($ret === 0) {
                return;
            } else {
                echo 'Retrying deployment';
            }
        }
        self::fail('Deployment failed.');
    }


    public static function tearDownAfterClass()
    {
        for ($i = 0; $i <= 3; $i++) {
            exec('gcloud -q app versions delete --service default '
                 . self::getVersion()
                 . ' --project '
                 . getenv(self::PROJECT_ENV),
                 $output,
                 $ret
            );
            if ($ret === 0) {
                return;
            } else {
                echo 'Retrying to delete the version';
            }
        }
    }

    public function setUp()
    {
        $url = sprintf('https://%s-dot-%s.appspot.com/',
                       self::getVersion(),
                       getenv(self::PROJECT_ENV));
        $this->client = new Client(['base_uri' => $url]);
    }

    public function testIndex()
    {
        // Access the blog top page
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'I am very glad that you are testing WordPress instalation.',
            $resp->getBody()->getContents());
    }
}
