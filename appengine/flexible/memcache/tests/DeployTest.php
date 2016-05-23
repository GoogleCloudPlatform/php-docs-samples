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

namespace Google\Cloud\Samples\AppEngine\Symfony;

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use GuzzleHttp\Client;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use ExecuteCommandTrait;

    /** @var GuzzleHttp\Client */
    private $client;
    /** @var string */
    private static $version;
    /** @var string */
    private $baseUri;

    private static function getVersion()
    {
        if (is_null(self::$version)) {
            $versionId = getenv('GOOGLE_VERSION_ID') ?: time();
            self::$version = "memcached-" . $versionId;
        }

        return self::$version;
    }

    public static function getProjectId()
    {
        return getenv('GOOGLE_PROJECT_ID');
    }

    public static function getServiceName()
    {
        return getenv('GOOGLE_SERVICE_NAME');
    }

    private static function getTargetDir()
    {
        $tmp = sys_get_temp_dir();
        $versionId = self::getVersion();
        $targetDir = sprintf('%s/%s', $tmp, $versionId);

        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }

        if (!is_writable($targetDir)) {
            throw new \Exception(sprintf('Cannot write to %s', $targetDir));
        }

        return $targetDir;
    }

    public static function setUpBeforeClass()
    {
        if (getenv('RUN_DEPLOYMENT_TESTS') !== 'true') {
            self::markTestSkipped(
                'To run this test, set RUN_DEPLOYMENT_TESTS env to "true".'
            );
        }

        self::$logger = new Logger('phpunit');

        // verify and set environment variables
        self::verifyEnvironmentVariables();
        $targetDir = self::getTargetDir();
        $projectId = self::getProjectId();
        $version = self::getVersion();

        // move into the target directory
        self::setWorkingDirectory($targetDir);
        self::createProject($targetDir);
        self::deploy($projectId, $version, $targetDir);
    }

    private static function verifyEnvironmentVariables()
    {
        $envVars = [
            'GOOGLE_PROJECT_ID',
        ];
        foreach ($envVars as $envVar) {
            if (false === getenv($envVar)) {
                self::fail("Please set the ${envVar} environment variable");
            }
        }
    }

    private static function copyDirectory($source, $target)
    {
        $dir = opendir($source);
        @mkdir($target);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    self::copyDirectory($source . '/' . $file, $target . '/' . $file);
                } else {
                    copy($source . '/' . $file, $target . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function copyRecursively($source, $target)
    {
        return is_dir($source) ? self::copyDirectory($source, $target)
            : copy($source, $target);
    }

    private static function createProject($targetDir)
    {
        // move the code for the sample to the new drupal installation
        $files = ['app.yaml', 'nginx-app.conf', 'app.php', 'memcache.html.twig',
            'vendor', 'web', 'Dockerfile', '.dockerignore'];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $targetDir, $file);
            self::copyRecursively($source, $target);
        }

        // if a service name has been defined, add it to "app.yaml"
        if ($service = self::getServiceName()) {
            $appYaml = sprintf('%s/app.yaml', $targetDir);
            file_put_contents($appYaml, "service: $service\n", FILE_APPEND);
        }
    }

    public static function deploy($projectId, $versionId, $targetDir)
    {
        for ($i = 0; $i <= 3; $i++) {
            $process = self::createProcess(
                "gcloud -q preview app deploy "
                . "--version $versionId "
                . "--project $projectId --no-promote -q "
                . "$targetDir/app.yaml"
            );
            $process->setTimeout(60 * 30); // 30 minutes
            if (self::executeProcess($process, false)) {
                return;
            }
            self::$logger->warning('Retrying deployment');
        }
        self::fail('Deployment failed.');
    }

    public static function tearDownAfterClass()
    {
        for ($i = 0; $i <= 3; $i++) {
            $process = self::createProcess(sprintf(
                'gcloud -q preview app versions delete %s --service %s --project %s',
                self::getVersion(),
                self::getServiceName() ?: 'default',
                self::getProjectId()
            ));
            $process->setTimeout(600); // 10 minutes
            if (self::executeProcess($process, false)) {
                return;
            }
            self::$logger->warning('Retrying to delete the version');
        }
    }

    public function setUp()
    {
        $service = self::getServiceName();
        $this->baseUri = sprintf('https://%s%s-dot-%s.appspot.com/',
            self::getVersion(),
            $service ? "-dot-$service" : '',
            self::getProjectId());
        $this->client = new Client(['base_uri' => $this->baseUri]);
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');

        // Use a random key to avoid colliding with simultaneous tests.
        $key = rand(0, 1000);

        // Test the /memcached REST API.
        $this->put("/memcached/test$key", "sour");
        $this->assertEquals("sour", $this->get("/memcached/test$key"));
        $this->put("/memcached/test$key", "sweet");
        $this->assertEquals("sweet", $this->get("/memcached/test$key"));

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $resp = $this->client->post('/');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');
    }

    /**
     * HTTP PUTs the body to the url path.
     * @param $path string
     * @param $body string
     */
    private function put($path, $body)
    {
        $url = join('/', [trim($this->baseUri, '/'), trim($path, '/')]);
        $request = new \GuzzleHttp\Psr7\Request('PUT', $url, array(), $body);
        $this->client->send($request);
    }

    /**
     * HTTP GETs the url path.
     * @param $path string
     * @return string The HTTP Response.
     */
    private function get($path)
    {
        return $this->client->get($path)->getBody()->getContents();
    }
}
