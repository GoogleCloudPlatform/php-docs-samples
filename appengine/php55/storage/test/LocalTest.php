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
use Silex\WebTestCase;
use google\appengine\api\cloud_storage\CloudStorageTools;

require_once __DIR__ . '/mocks/CloudStorageStreamWrapper.php';
require_once __DIR__ . '/mocks/CloudStorageTools.php';

class LocalTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';

        // set some parameters for testing
        $app['session.test'] = true;
        $app['debug'] = true;

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        // the bucket name doesn't matter because we mock the stream wrapper
        $app['bucket_name'] = 'test-bucket';

        // register the mock stream handler
        if (!in_array('gs', stream_get_wrappers())) {
            stream_wrapper_register('gs',
                '\google\appengine\ext\cloud_storage_streams\CloudStorageStreamWrapper');
        }

        return $app;
    }

    public function testHome()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testRead()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/file.txt');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $fileTxt = file_get_contents(__DIR__ . '/../file.txt');

        $this->assertEquals($response->getContent(), $fileTxt);
    }

    public function testWrite()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testWriteOptions()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/options', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testWriteStream()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/stream', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testWriteCaching()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/caching', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testWriteMetadata()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/metadata', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);
        CloudStorageTools::$metadata = ['foo' => 'bar'];
        CloudStorageTools::$contentType = 'text/plain';

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertContains($time, $content);
        $this->assertContains(CloudStorageTools::$contentType, $content);
        $this->assertTrue(count(CloudStorageTools::$metadata) > 0);
        foreach (CloudStorageTools::$metadata as $key => $value) {
            $this->assertContains($key, $content);
            $this->assertContains($value, $content);
        }
    }

    public function testWriteDefault()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/default', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testWriteDefaultStream()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/write/default/stream', [
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testServe()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('GET', '/serve');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('This is the contents of a file', CloudStorageTools::$served);
    }

    public function testWritePublic()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('GET', '/write/public');

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $url = $response->headers->get('Location');
        $this->assertFalse(empty($url));
        $this->assertEquals($url, CloudStorageTools::$publicUrl);
    }

    public function testServeImage()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('GET', '/serve/image');

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $url = $response->headers->get('Location');
        $this->assertFalse(empty($url));
        $this->assertEquals($url, CloudStorageTools::$imageUrl);
        $this->assertEquals(['size' => 400, 'crop' => true], CloudStorageTools::$imageOptions);
    }
}
