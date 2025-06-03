<?php

/**
 * Copyright 2025 Google LLC.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/main/pubsub/api/README.md
 */

namespace Google\Cloud\Samples\PubSub;

# [START pubsub_create_topic_with_cloud_storage_ingestion]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\IngestionDataSourceSettings\CloudStorage\AvroFormat;
use Google\Cloud\PubSub\V1\IngestionDataSourceSettings\CloudStorage\PubSubAvroFormat;
use Google\Cloud\PubSub\V1\IngestionDataSourceSettings\CloudStorage\TextFormat;
use Google\Protobuf\Timestamp;

/**
 * Creates a topic with Cloud Storage Ingestion.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $bucket  Cloud Storage bucket.
 * @param string $inputFormat  Input format for the Cloud Storage data. Must be one of text, avro, or pubsub_avro.
 * @param string $textDelimiter  Delimiter for text format input.
 * @param string $matchGlob  Glob pattern used to match objects that will be ingested. If unset, all objects will be ingested.
 * @param string $minimumObjectCreatedTime  Only objects with a larger or equal creation timestamp will be ingested.
 */
function create_topic_with_cloud_storage_ingestion(
    string $projectId,
    string $topicName,
    string $bucket,
    string $inputFormat,
    string $minimumObjectCreatedTime,
    string $textDelimiter = '',
    string $matchGlob = ''
): void {
    $datetime = new \DateTimeImmutable($minimumObjectCreatedTime);
    $timestamp = (new Timestamp())
        ->setSeconds($datetime->getTimestamp())
        ->setNanos($datetime->format('u') * 1000);

    $cloudStorageData = [
        'bucket' => $bucket,
        'minimum_object_create_time' => $timestamp
    ];

    $cloudStorageData[$inputFormat . '_format'] = match($inputFormat) {
        'text' => new TextFormat(['delimiter' => $textDelimiter]),
        'avro' => new AvroFormat(),
        'pubsub_avro' => new PubSubAvroFormat(),
        default => throw new \InvalidArgumentException(
            'inputFormat must be in (\'text\', \'avro\', \'pubsub_avro\'); got value: ' . $inputFormat
        )
    };

    if (!empty($matchGlob)) {
        $cloudStorageData['match_glob'] = $matchGlob;
    }

    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->createTopic($topicName, [
        'ingestionDataSourceSettings' => [
            'cloud_storage' => $cloudStorageData
        ]
    ]);

    printf('Topic created: %s' . PHP_EOL, $topic->name());
}
# [END pubsub_create_topic_with_cloud_storage_ingestion]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
