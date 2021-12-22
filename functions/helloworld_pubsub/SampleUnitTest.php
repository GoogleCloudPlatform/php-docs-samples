<?php

/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

// [START functions_pubsub_unit_test]

namespace Google\Cloud\Samples\Functions\HelloworldPubsub\Test;

use CloudEvents\V1\CloudEventImmutable;
use CloudEvents\V1\CloudEventInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class SampleUnitTest.
 *
 * Unit test for 'Helloworld Pub/Sub' Cloud Function.
 */
class SampleUnitTest extends TestCase
{
    /**
     * Include the Cloud Function code before running any tests.
     *
     * @see https://phpunit.readthedocs.io/en/latest/fixtures.html
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/index.php';
    }

    public function dataProvider()
    {
        return [
            [
                'cloudevent' => new CloudEventImmutable(
                    uniqId(), // id
                    'pubsub.googleapis.com', // source
                    'google.cloud.pubsub.topic.v1.messagePublished', // type
                    [
                        'data' => base64_encode('John')
                    ]
                ),
                'expected' => 'Hello, John!'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFunction(
        CloudEventInterface $cloudevent,
        string $expected
    ): void {
        // Capture function output by overriding the function's logging behavior.
        // The 'LOGGER_OUTPUT' environment variable must be used in your function:
        //
        // $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');
        // fwrite($log, 'Log Entry');
        putenv('LOGGER_OUTPUT=php://output');
        helloworldPubsub($cloudevent);
        // Provided by PHPUnit\Framework\TestCase.
        $actual = $this->getActualOutput();

        // Test that output includes the expected value.
        $this->assertStringContainsString($expected, $actual);
    }
}

// [END functions_pubsub_unit_test]
