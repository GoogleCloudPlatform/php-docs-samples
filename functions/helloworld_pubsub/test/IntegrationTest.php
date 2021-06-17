<?php
/**
 * Copyright 2021 Google LLC.
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

declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\HelloworldPubsub\Test;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

/**
 * Class IntegrationTest.
 *
 * Integration Test for helloworldPubsub.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    /** @var string */
    private static $entryPoint = 'helloworldPubsub';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    public function dataProvider()
    {
        return [
            [
                'cloudevent' => [
                    'id' => uniqid(),
                    'source' => 'pubsub.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.pubsub.topic.v1.messagePublished',
                ],
                'data' => [],
                'statusCode' => 200,
                'expected' => 'Hello, World!',
                'label' => 'Should print a default value'
            ],
            [
                'cloudevent' => [
                    'id' => uniqid(),
                    'source' => 'pubsub.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.pubsub.topic.v1.messagePublished',
                ],
                'data' => [
                    'message' => [
                        'data' => base64_encode('John')
                    ]
                ],
                'statusCode' => 200,
                'expected' => 'Hello, John!',
                'label' => 'Should print a name'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHelloworldPubsub(array $cloudevent, array $data, int $statusCode, string $expected, string $label): void
    {
        // Prepare the HTTP headers for a CloudEvent.
        $cloudEventHeaders = [];
        foreach ($cloudevent as $key => $value) {
            $cloudEventHeaders['ce-' . $key] = $value;
        }

        // Send an HTTP request using CloudEvent metadata.
        $resp = $this->client->request('POST', '/', [
            'body' => json_encode($data),
            'headers' => $cloudEventHeaders + [
                // Instruct the function framework to parse the body as JSON.
                'content-type' => 'application/json'
            ],
        ]);

        // The Cloud Function logs all data to stderr.
        $actual = self::$localhost->getIncrementalErrorOutput();

        // Confirm the status code.
        $this->assertEquals(
            $statusCode,
            $resp->getStatusCode(),
            $label . ' status code'
        );

        // Verify the function's behavior is correct.
        $this->assertStringContainsString($expected, $actual, $label . ' contains');
    }
}
