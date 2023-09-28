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

namespace Google\Cloud\Samples\Functions\FirebaseAnalytics\Test;

use PHPUnit\Framework\TestCase;
use Google\CloudFunctions\CloudEvent;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

/**
 * Class IntegrationTest.
 *
 * Integration Test for firebaseAnalytics
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    /** @var string */
    private static $entryPoint = 'firebaseAnalytics';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    public function dataProvider()
    {
        return [
            [
                'cloudevent' => CloudEvent::fromArray([
                    'id' => uniqid(),
                    'source' => 'firebase.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.firebase.remoteconfig.v1.updated',
                    'data' => [
                        // eventDim is a list of dictionaries
                        'eventDim' => array([
                            'name' => 'test_event',
                            'timestampMicros' => time() * 1000,
                        ]),
                        'userDim' => [
                            'geoInfo' => [
                                'city' => 'San Francisco',
                                'country' => 'US'
                            ],
                            'deviceInfo' => [
                                'deviceModel' => 'Google Pixel XL'
                            ]
                        ]
                    ],
                ]),
                'statusCode' => '200',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseAnalytics(
        CloudEvent $cloudevent,
        string $statusCode
    ): void {
        // Send an HTTP request using CloudEvent.
        $resp = $this->request($cloudevent);

        // The Cloud Function logs all data to stderr.
        $actual = self::$localhost->getIncrementalErrorOutput();

        // Confirm the status code.
        $this->assertEquals($statusCode, $resp->getStatusCode());

        // Verify the data properties are logged by the function.
        $data = $cloudevent->getData();
        foreach ($data as $property => $value) {
            if (is_string($value)) {
                $this->assertStringContainsString($value, $actual);
            }
        }
        foreach ($data['eventDim'] as $property => $value) {
            if (is_string($value)) {
                $this->assertStringContainsString($value, $actual);
            }
        }
        foreach ($data['userDim'] as $property => $value) {
            if (is_string($value)) {
                $this->assertStringContainsString($value, $actual);
            }
        }
    }
}
