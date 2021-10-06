<?php
/**
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\Functions\TipsRetry\Test;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;
use Google\CloudFunctions\CloudEvent;

/**
 * Class IntegrationTest.
 *
 * Integration Test for tipsRetry.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    /** @var string */
    private static $entryPoint = 'tipsRetry';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    private static function makeData(array $jsonArray)
    {
        return [
            'message' => [
                'data' => base64_encode(json_encode($jsonArray))
            ]
        ];
    }

    public function dataProvider()
    {
        return [
            [
                'cloudevent' => [
                    'id' => uniqid(),
                    'source' => 'pubsub.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.pubsub.topic.v1.messagePublished',
                    'data' => self::makeData(['some_parameter' => true]),
                ],
                'statusCode' => '500',
                'expected' => 'retrying...',
                'label' => 'Should throw an exception to trigger a retry'
            ],
            [
                'cloudevent' => [
                    'id' => uniqid(),
                    'source' => 'pubsub.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.pubsub.topic.v1.messagePublished',
                    'data' => self::makeData(['some_parameter' => false]),
                ],
                'statusCode' => '200',
                'expected' => 'Not retrying',
                'label' => 'Should not throw an exception to avoid retry'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTipsRetry(array $cloudevent, string $statusCode, string $expected, string $label): void
    {
        // Send an HTTP request using CloudEvent metadata.
        $resp = $this->request(CloudEvent::fromArray($cloudevent));

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
