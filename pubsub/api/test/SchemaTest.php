<?php
/**
 * Copyright 2021 Google LLC
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

namespace Google\Cloud\Samples\PubSub;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\PublisherClient;
use Google\Cloud\PubSub\V1\SchemaServiceClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Schema samples.
 *
 * @group pubsub-schema
 */
class SchemaTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

    const AVRO_DEFINITION = __DIR__ . '/../src/data/us-states.avsc';
    const PROTOBUF_DEFINITION = __DIR__ . '/../src/data/us-states.proto';

    /**
     * @dataProvider definitions
     */
    public function testCreateGetListAndDelete($type, $definitionFile)
    {
        $schemaId = uniqid('samples-test-' . $type . '-');
        $schemaName = SchemaServiceClient::schemaName(self::$projectId, $schemaId);

        $createOutput = $this->runFunctionSnippet(sprintf('create_%s_schema', $type), [
            self::$projectId,
            $schemaId,
            $definitionFile,
        ]);

        $this->assertEquals(
            sprintf('Schema %s created.', $schemaName),
            $createOutput
        );

        $getOutput = $this->runFunctionSnippet('get_schema', [
            self::$projectId,
            $schemaId,
        ]);

        $this->assertEquals(
            sprintf('Schema %s retrieved', $schemaName),
            $getOutput
        );

        $listOutput = $this->runFunctionSnippet('list_schemas', [
            self::$projectId,
        ]);

        $this->assertStringContainsString(
            sprintf('Schema name: %s', $schemaName),
            $listOutput
        );

        $deleteOutput = $this->runFunctionSnippet('delete_schema', [
            self::$projectId,
            $schemaId,
        ]);

        $this->assertEquals(
            sprintf('Schema %s deleted.', $schemaName),
            $deleteOutput
        );
    }

    /**
     * @dataProvider definitions
     */
    public function testSchemaRevision($type, $definitionFile)
    {
        $schemaId = uniqid('samples-test-' . $type . '-');
        $schemaName = SchemaServiceClient::schemaName(self::$projectId, $schemaId);

        $this->runFunctionSnippet(sprintf('create_%s_schema', $type), [
            self::$projectId,
            $schemaId,
            $definitionFile,
        ]);

        $listOutput = $this->runFunctionSnippet(sprintf('commit_%s_schema', $type), [
            self::$projectId,
            $schemaId,
            $definitionFile,
        ]);

        $this->assertStringContainsString(
            sprintf(
                'Committed a schema using an %s schema: %s@', ucfirst($type), $schemaName
            ),
            $listOutput
        );

        $schemaRevisionId = trim(explode('@', $listOutput)[1]);

        $listOutput = $this->runFunctionSnippet('get_schema_revision', [
            self::$projectId,
            $schemaId,
            $schemaRevisionId,
        ]);

        $this->assertStringContainsString(
            sprintf(
                'Got a schema revision: %s@%s',
                $schemaName,
                $schemaRevisionId
            ),
            $listOutput
        );

        $listOutput = $this->runFunctionSnippet('list_schema_revisions', [
            self::$projectId,
            $schemaId
        ]);

        $this->assertStringContainsString('Listed schema revisions', $listOutput);

        $listOutput = $this->runFunctionSnippet('delete_schema', [
            self::$projectId,
            $schemaId
        ]);
        $this->assertStringContainsString(sprintf('Schema %s deleted', $schemaName), $listOutput);
    }

    /**
     * @dataProvider definitionsWithEncoding
     */
    public function testCreateUpdateTopic($type, $definitionFile, $encoding)
    {
        $typeApiFormat = $type == 'avro' ? 'AVRO' : 'PROTOCOL_BUFFER';
        $pubsub = new PubSubClient([
            'projectId' => self::$projectId,
        ]);

        $schemaId = uniqid('samples-test-' . $type . '-');
        $topicId = uniqid('samples-test-' . $type . '-' . $encoding . '-');

        $definition = (string) file_get_contents($definitionFile);
        $schema = $pubsub->createSchema($schemaId, $typeApiFormat, $definition);

        // Test create topic with schema
        $output = $this->runFunctionSnippet('create_topic_with_schema', [
            self::$projectId,
            $topicId,
            $schemaId,
            $encoding,
        ]);

        $this->assertEquals(
            sprintf('Topic %s created', PublisherClient::topicName(self::$projectId, $topicId)),
            $output
        );

        // Create a schema revision
        $listOutput = $this->runFunctionSnippet(sprintf('commit_%s_schema', $type), [
            self::$projectId,
            $schemaId,
            $definitionFile,
        ]);
        $schemaRevisionId = trim(explode('@', $listOutput)[1]);

        // Test create topic with schema revisions listed
        $newTopicId = uniqid('samples-test-' . $type . '-' . $encoding . '-');
        $output = $this->runFunctionSnippet(
            sprintf(
                'create_topic_with_schema_revisions'
            ), [
                self::$projectId,
                $newTopicId,
                $schemaId,
                $encoding,
                $firstRevisionId = $schema->info()['revisionId'],
                $lastRevisionId = $schemaRevisionId
            ]
        );

        $this->assertEquals(
            sprintf('Created topic with schema: %s', PublisherClient::topicName(self::$projectId, $newTopicId)),
            $output
        );

        // Test update topic with schema attached
        $output = $this->runFunctionSnippet(sprintf('update_topic_schema'), [
            self::$projectId,
            $topicId,
            $schema->info()['revisionId'],
            $schemaRevisionId
        ]);

        $this->assertEquals(
            sprintf('Updated topic with schema: %s', PublisherClient::topicName(self::$projectId, $topicId)),
            $output
        );

        $pubsub->topic($topicId)->delete();
        $pubsub->topic($newTopicId)->delete();
        $pubsub->schema($schemaId)->delete();
    }

    public function definitions()
    {
        return [
            [
                'avro',
                self::AVRO_DEFINITION,
            ], [
                'proto',
                self::PROTOBUF_DEFINITION,
            ]
        ];
    }

    public function definitionsWithEncoding()
    {
        return [
            [
                'avro',
                self::AVRO_DEFINITION,
                'JSON'
            ], [
                'proto',
                self::PROTOBUF_DEFINITION,
                'JSON'
            ], [
                'avro',
                self::AVRO_DEFINITION,
                'BINARY'
            ], [
                'proto',
                self::PROTOBUF_DEFINITION,
                'BINARY'
            ],
        ];
    }

    /**
     * @dataProvider encodingTypes
     */
    public function testPublishAndSubscribeAvro($encoding)
    {
        $pubsub = new PubSubClient([
            'projectId' => self::$projectId,
        ]);

        $topicId = uniqid('samples-test-publish-avro' . $encoding . '-');
        $subscriptionId = uniqid('samples-test-publish-avro' . $encoding . '-');
        $schemaId = uniqid('samples-test-publish-avro' . $encoding . '-');

        $definition = file_get_contents(self::AVRO_DEFINITION);
        $schema = $pubsub->createSchema($schemaId, 'AVRO', $definition);

        $topic = $pubsub->createTopic($topicId, [
            'schemaSettings' => [
                'schema' => $schema,
                'encoding' => $encoding,
            ]
        ]);

        $subscription = $topic->subscribe($subscriptionId);

        $publishOutput = $this->runFunctionSnippet('publish_avro_records', [
            self::$projectId,
            $topicId,
            self::AVRO_DEFINITION,
        ]);

        $this->assertEquals(
            sprintf('Published message with %s encoding', $encoding),
            $publishOutput
        );

        $subscribeOutput = $this->runFunctionSnippet('subscribe_avro_records', [
            self::$projectId,
            $subscriptionId,
            self::AVRO_DEFINITION,
        ]);

        $this->assertStringContainsString(
            sprintf('Received a %d-encoded message', $encoding),
            $subscribeOutput
        );

        $topic->delete();
        $schema->delete();
        $subscription->delete();
    }

    /**
     * @dataProvider encodingTypes
     */
    public function testPublishAndSubscribeProtobuf($encoding)
    {
        $pubsub = new PubSubClient([
            'projectId' => self::$projectId,
        ]);

        $topicId = uniqid('samples-test-publish-protobuf' . $encoding . '-');
        $subscriptionId = uniqid('samples-test-publish-protobuf' . $encoding . '-');
        $schemaId = uniqid('samples-test-publish-protobuf' . $encoding . '-');

        $definition = file_get_contents(self::PROTOBUF_DEFINITION);
        $schema = $pubsub->createSchema($schemaId, 'PROTOCOL_BUFFER', $definition);

        $topic = $pubsub->createTopic($topicId, [
            'schemaSettings' => [
                'schema' => $schema,
                'encoding' => $encoding,
            ]
        ]);

        $subscription = $topic->subscribe($subscriptionId);

        $output = $this->runFunctionSnippet('publish_proto_messages', [
            self::$projectId,
            $topicId,
        ]);

        $this->assertEquals(
            sprintf('Published message with %s encoding', $encoding),
            $output
        );

        $subscribeOutput = $this->runFunctionSnippet('subscribe_proto_messages', [
            self::$projectId,
            $subscriptionId,
        ]);

        $this->assertStringContainsString(
            sprintf('Received a %d-encoded message', $encoding),
            $subscribeOutput
        );

        $topic->delete();
        $schema->delete();
        $subscription->delete();
    }

    public function encodingTypes()
    {
        return [
            ['JSON'],
            ['BINARY'],
        ];
    }
}
