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

namespace Google\Cloud\Samples\Functions\FirebaseReactive\Test;

use PHPUnit\Framework\TestCase;
use Google\CloudFunctions\CloudEvent;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;

/**
 * Class IntegrationTest.
 *
 * Integration Test for firebaseReactive.
 */
class IntegrationTest extends TestCase
{
    use CloudFunctionLocalTestTrait;

    /** @var string */
    private static $entryPoint = 'firebaseReactive';

    /** @var string */
    private static $functionSignatureType = 'cloudevent';

    /** @var string */
    private static $value = 'value';

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
                        'value' => [
                            'fields' => [
                                'original' => [
                                    'stringValue' => self::$value
                                ]
                            ],
                            'name' => '/documents/some_collection/blah',
                        ],
                    ],
                ])
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseFirestore(
        CloudEvent $cloudevent
    ): void {
        // Send an HTTP request using CloudEvent.
        $resp = $this->request($cloudevent);

        // The Cloud Function logs all data to stderr.
        $actual = self::$localhost->getIncrementalErrorOutput();

        // Verify the data value is logged by the function.
        $expected = strtoupper(self::$value);
        $this->assertStringContainsString($expected, $actual);
    }
}
