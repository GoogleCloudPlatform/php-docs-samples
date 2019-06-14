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
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    private $client;

    const PHPMYADMIN_VERSION = '4.6.3';
    const PROJECT_ENV = 'GOOGLE_PROJECT_ID';
    const VERSION_ENV = 'GOOGLE_VERSION_ID';
    const DB_PASSWORD_ENV = 'MYSQLADMIN_ROOT_PASSWORD';
    const BF_SECRET_ENV = 'BLOWFISH_SECRET';
    const CLOUDSQL_INSTANCE_ENV = 'PHPMYADMIN_CLOUDSQL_INSTANCE';

    private static function output($line)
    {
        fwrite(STDERR, $line . "\n");
    }

    private static function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link($dir)) ?
                self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private static function getTargetDir()
    {
        $tmp = sys_get_temp_dir();
        $e2e_test_version = getenv(self::VERSION_ENV);
        $ret = "$tmp/phpmyadmin-test-$e2e_test_version";
        if (is_file($ret)) {
            self::fail("$ret is a normal file and can not proceed.");
        }
        if (is_dir($ret)) {
            self::delTree($ret);
        }
        mkdir($ret, 0750, true);
        return realpath($ret);
    }

    private static function downloadPhpmyadmin($dir)
    {
        $tmp = sys_get_temp_dir();
        $url = 'https://files.phpmyadmin.net/phpMyAdmin/'
            . self::PHPMYADMIN_VERSION . '/phpMyAdmin-'
            . self::PHPMYADMIN_VERSION . '-all-languages.tar.bz2';
        $tmpdir = substr(basename($url), 0, -8);
        $file = $tmp . DIRECTORY_SEPARATOR . basename($url);
        file_put_contents($file, file_get_contents($url));
        $phar = new \PharData($file, 0, null);
        $result = $phar -> extractTo($tmp, null, true);
        rename($tmp . DIRECTORY_SEPARATOR . $tmpdir, $dir);
        unlink($file);
    }

    private function copyFiles($files, $params)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../');
        $twig = new \Twig_Environment($loader);
        foreach ($files as $file => $target) {
            $dest = $target . DIRECTORY_SEPARATOR . $file;
            touch($dest);
            chmod($dest, 0640);
            $content = $twig->render($file, $params);
            file_put_contents($dest, $content, LOCK_EX);
        }
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
        $blowfish_secret = getenv(self::BF_SECRET_ENV);
        $cloudsql_instance = getenv(self::CLOUDSQL_INSTANCE_ENV);
        $db_password = getenv(self::DB_PASSWORD_ENV);
        if ($project_id === false) {
            self::fail('Please set ' . self::PROJECT_ENV . ' env var.');
        }
        if ($e2e_test_version === false) {
            self::fail('Please set ' . self::VERSION_ENV . ' env var.');
        }
        if ($blowfish_secret === false) {
            self::fail('Please set ' . self::BF_SECRET_ENV . ' env var.');
        }
        if ($cloudsql_instance === false) {
            self::fail(
                'Please set ' . self::CLOUDSQL_INSTANCE_ENV . ' env var.');
        }
        if ($db_password === false) {
            self::fail('Please set ' . self::DB_PASSWORD_ENV . ' env var.');
        }
        $target = self::getTargetDir();
        self::downloadPhpmyadmin($target);
        self::copyFiles(
            array(
                'app-e2e.yaml' => $target,
                'php.ini' => $target,
                'config.inc.php'  => $target
            ),
            array(
                'your_connection_string' => "$project_id/$cloudsql_instance",
                'your_secret' => $blowfish_secret,
            )
        );
        rename("$target/app-e2e.yaml", "$target/app.yaml");
        self::deploy($project_id, $e2e_test_version, $target);
    }

    public static function deploy($project_id, $e2e_test_version, $target)
    {
        $command = "gcloud -q app deploy --no-promote "
            . "--no-stop-previous-version "
            . "--version $e2e_test_version "
            . "--project $project_id "
            . "$target/app.yaml";
        for ($i = 0; $i <= 3; $i++) {
            exec($command, $output, $ret);
            foreach ($output as $line) {
                self::output($line);
            }
            if ($ret === 0) {
                return;
            } else {
                self::output('Retrying deployment');
            }
        }
        self::fail('Deployment failed.');
    }


    public static function tearDownAfterClass()
    {
        $command = 'gcloud -q app versions delete --service phpmyadmin '
            . getenv(self::VERSION_ENV)
            . ' --project '
            . getenv(self::PROJECT_ENV);
        for ($i = 0; $i <= 3; $i++) {
            exec($command, $output, $ret);
            foreach ($output as $line) {
                self::output($line);
            }
            if ($ret === 0) {
                self::output('Successfully delete the version');
                return;
            } else {
                self::output('Retrying to delete the version');
            }
        }
        self::fail('Failed to delete the version.');
    }

    public function setUp()
    {
        $url = sprintf('https://%s-dot-phpmyadmin-dot-%s.appspot.com/',
                       getenv(self::VERSION_ENV),
                       getenv(self::PROJECT_ENV));
        $this->client = new Client(['base_uri' => $url]);
    }

    public function testIndex()
    {
        // Index serves succesfully the login screen.
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode(),
                            'Login screen status code');
        // TODO check the contents
    }
}
