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
namespace Google\Cloud\Test\Auth;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        if (null == $projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }

        // Access the modules app top page.
        try {
            $resp = $this->client->get('');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->fail($e->getResponse()->getBody());
        }
        $this->assertEquals('200', $resp->getStatusCode(),
                            'top page status code');
        $contents = $resp->getBody()->getContents();
        $this->assertContains(
            sprintf('Bucket: %s', $projectId),
            $contents);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count(
                $contents,
                sprintf('Bucket: %s', $projectId)
            )
        );
    }
}
