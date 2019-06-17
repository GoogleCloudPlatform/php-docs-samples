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

namespace Google\Cloud\Test\AppEngine\Storage;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    /**
     * Set up the client.
     *
     * @before
     */
    public function setUpClient()
    {
        $url = self::$gcloudWrapper->getBaseUrl();
        $this->client = new Client([
            'base_uri' => $url,
            'allow_redirects' => true,
        ]);
    }

    public static function beforeDeploy()
    {
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            self::markTestSkipped('Set the GOOGLE_STORAGE_BUCKET environment variable');
        }

        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYamlContents = file_get_contents('app.yaml');
        $appYaml = Yaml::parse($appYamlContents);
        $appYaml['env_variables']['GOOGLE_STORAGE_BUCKET'] = $bucketName . '/storage';
        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }

    public function testHome()
    {
        $response = $this->client->get('/');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWrite()
    {
        $content = sprintf('test write (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWriteOptions()
    {
        $content = sprintf('write options (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/options', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWriteStream()
    {
        $content = sprintf('write stream (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/stream', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWriteCaching()
    {
        $content = sprintf('write caching (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/caching', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWriteMetadata()
    {
        $content = sprintf('write metadata (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/metadata', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertContains($content, $content);
        $this->assertContains('foo: bar', $body);
        $this->assertContains('baz: qux', $body);
    }

    public function testWriteDefault()
    {
        $content = sprintf('write default (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/default', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWriteDefaultStream()
    {
        $content = sprintf('write default stream (%s)', date('Y-m-d H:i:s'));
        $response = $this->client->request('POST', '/write/default/stream', [
            'form_params' => ['content' => $content],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($content, (string) $response->getBody());
    }

    public function testWritePublic()
    {
        $response = $this->client->request('GET', '/write/public');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('new file written at ', (string) $response->getBody());
    }
}
