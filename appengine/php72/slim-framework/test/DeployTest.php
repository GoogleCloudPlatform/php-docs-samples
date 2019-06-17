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
namespace Google\Cloud\Samples\AppEngine\SlimFramework;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        $resp = $this->client->get('/?name=Slim');

        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains('Hello, Slim!', (string) $resp->getBody());
    }

    /**
     * @expectedException GuzzleHttp\Exception\ClientException
     * @expectedExceptionMessage 404 Not Found
     */
    public function test404()
    {
        $resp = $this->client->get('/does-not-exist');
    }
}
