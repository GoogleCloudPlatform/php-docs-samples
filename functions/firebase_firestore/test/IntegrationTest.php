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

namespace Google\Cloud\Samples\Functions\FirebaseFirestore\Test;

use PHPUnit\Framework\TestCase;
use Google\CloudFunctions\CloudEvent;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

/**
 * Class IntegrationTest.
 *
 * Integration Test for firebaseRTDB.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    /** @var string */
    private static $entryPoint = 'firebaseFirestore';

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
                    'type' => 'google.cloud.firestore.document.v1.created',
                    'data' => [
                        'resource' => 'projects/_/instances/my-instance/refs/messages',
                        'oldValue' => array('old' => 'value'),
                        'value' => array('new' => 'value'),
                    ],
                ]),
                'statusCode' => '200',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseFirestore(
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
        foreach ($cloudevent->getData() as $property => $value) {
            if (is_string($value)) {
                $this->assertStringContainsString($value, $actual);
            }
        }
        $this->assertStringContainsString($cloudevent->getId(), $actual);
    }
}
