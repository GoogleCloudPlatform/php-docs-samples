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
namespace Google\Cloud\Test\Trace;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public function testIndex()
    {
        // Access the top page.
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains('Slow function called', (string) $resp->getBody());

        // create a client to get the traces
        $middleware = ApplicationDefaultCredentials::getMiddleware([
            'https://www.googleapis.com/auth/cloud-platform'
        ]);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $trace = new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
            'base_uri' => sprintf(
                'https://cloudtrace.googleapis.com/v1/projects/%s/',
                getenv('GOOGLE_PROJECT_ID')
            )
        ]);

        $start = new \DateTime('-2 minutes', new \DateTimeZone('UTC'));
        $this->runEventuallyConsistentTest(function () use ($trace, $start) {
            // make the request
            $response = $trace->get('traces', [
                'query' => [
                    'startTime' => $start->format('Y-m-d\TH:i:s\Z'),
                    'filter' => 'span:slow_function',
                ]
            ]);
            $traces = json_decode($response->getBody(), true);
            $this->assertTrue(count($traces) > 0);
        });
    }
}
