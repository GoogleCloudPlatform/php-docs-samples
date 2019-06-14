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
namespace Google\Cloud\Samples\AppEngine\Metadata;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        $resp = $this->client->get('/');

        $this->assertEquals(
            '200',
            $resp->getStatusCode(),
            'Top page status code should be 200'
        );
        $this->assertContains('All metadata keys:', (string) $resp->getBody());
        $this->assertContains(
            urlencode('instance/service-accounts/default/aliases'),
            (string) $resp->getBody()
        );
    }

    public function testMetadataKey()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Set the GOOGLE_PROJECT_ID env var.');
        }
        $path = 'project/project-id';
        $resp = $this->client->get('/', [
            'query' => ['path' => $path]
        ]);

        $body = (string) $resp->getBody();
        $this->assertEquals(
            '200',
            $resp->getStatusCode(),
            'Top page status code should be 200'
        );
        $this->assertContains("Metadata for <code>$path</code>", $body);
        $this->assertContains($projectId, $body);
    }
}
