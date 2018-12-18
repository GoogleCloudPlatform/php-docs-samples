<?php
/**
 * Copyright 2018 Google LLC
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

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use GuzzleHttp\Exception\ClientException;

use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use TestTrait;
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public function testIndex()
    {
        // Access the modules app top page.
        $response = $this->client->get('');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertContains(
            'Hello, World!',
            $response->getBody()->getContents()
        );
    }

    public function testTaskHandlerInvalid()
    {
        $this->expectException(ClientException::class);
        $response = $this->client->get('/task_handler');
    }
}
