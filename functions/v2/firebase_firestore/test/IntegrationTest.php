<?php
/**
 * Copyright 2023 Google LLC.
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

use Google\Events\Cloud\Firestore\V1\DocumentEventData;
use PHPUnit\Framework\TestCase;
use Google\CloudFunctions\CloudEvent;
use Google\Cloud\TestUtils\CloudFunctionLocalTestTrait;
use Google\Events\Cloud\Firestore\V1\Document;

/**
 * Class IntegrationTest.
 *
 * Integration Test for Cloud Firestore Trigger.
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
        $old_document = new Document();
        $old_document->mergeFromArray([
            'name' => 'pojects/-/databases/(default)/documents/collection/test',
            'fields' => [
                'hi' => [
                    'stringValue',
                    'hi'
                ]
            ]
        ]);

        $new_document = new Document();
        $new_document->mergeFromArray([
            'name' => 'pojects/-/databases/(default)/documents/collection/test',
            'fields' => [
                'hello' => [
                    'stringValue',
                    'world'
                ]
            ]
        ]);

        $document_event_data = new DocumentEventData();
        $document_event_data->setOldValue($old_document);
        $document_event_data->setValue($new_document);

        return [
            [
                'cloudevent' => CloudEvent::fromArray([
                    'id' => uniqid(),
                    'source' => 'firebase.googleapis.com',
                    'specversion' => '1.0',
                    'type' => 'google.cloud.firestore.document.v1.created',
                    'data' => $document_event_data->serializeToString(),
                ]),
                'statusCode' => '200',
                'documentEventData' => $document_event_data
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFirebaseFirestore(
        CloudEvent $cloudevent,
        string $statusCode,
        DocumentEventData $document_event_data
    ): void {
        // Send an HTTP request using CloudEvent.
        $resp = $this->request($cloudevent);

        // The Cloud Function logs all data to stderr.
        $actual = self::$localhost->getIncrementalErrorOutput();

        // Confirm the status code.
        $this->assertEquals($statusCode, $resp->getStatusCode());
        // Confirm old and new values are logged as json strings
        $this->assertStringContainsString($document_event_data->getOldValue()->serializeToJsonString(), $actual);
        $this->assertStringContainsString($document_event_data->getValue()->serializeToJsonString(), $actual);
        // Confirm id is logged
        $this->assertStringContainsString($cloudevent->getId(), $actual);
    }
}
