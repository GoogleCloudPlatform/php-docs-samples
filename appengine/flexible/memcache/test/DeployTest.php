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
namespace Google\Cloud\Test\Memcache;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public static function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYaml = Yaml::parse(file_get_contents('app.yaml'));
        $appYaml['env_variables']['MEMCACHE_ENDPOINT'] = self::requireEnv('MEMCACHE_ENDPOINT');
        $appYaml['env_variables']['MEMCACHE_USERNAME'] = self::requireEnv('MEMCACHE_USERNAME');
        $appYaml['env_variables']['MEMCACHE_PASSWORD'] = self::requireEnv('MEMCACHE_PASSWORD');

        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->client->get('');
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
        $resp = $this->client->post('');
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
        $url = join('/', [trim(self::$gcloudWrapper->getBaseUrl(), '/'),
            trim($path, '/')]);
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
