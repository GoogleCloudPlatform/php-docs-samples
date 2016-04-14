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

trait ModulesTestTrait
{
    public function testIndex()
    {
        // Access the modules app top page.
        try {
            $resp = $this->client->get('');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $this->assertContains(
            'default:',
            $resp->getBody()->getContents());
    }

    public function testAccessBackEnd()
    {
        // Access the '/access_backend'
        try {
            $resp = $this->client->get('/access_backend');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            '/access_backend status code');
        $this->assertContains(
            'This is my backend.',
            $resp->getBody()->getContents());
    }
}
